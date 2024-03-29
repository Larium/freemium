<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;
use DomainException;
use AktiveMerchant\Billing\Response;

class Subscription implements Rateable
{
    use Rate;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var Subscribable
     */
    private $subscribable;

    /**
     * Which service plan this subscription is for.
     * Affects how payment is interpreted.
     *
     * @var SubscriptionPlan
     */
    private $subscriptionPlan;

    /**
     * The previous subsciption plan when subscription plan is changed.
     *
     * @var SubscriptionPlan
     */
    private $originalPlan;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     *
     * @var DateTime|null
     */
    private $paidThrough;

    /**
     * When subscription started?
     *
     * @var DateTime
     */
    private $startedOn;

    /**
     * When the last gateway transaction was for this account?
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTime|null
     */
    private $lastTransactionAt;

    /**
     * @var Array<Freemium\CouponRedemption>
     */
    private $couponRedemptions = [];

    /**
     * Is subscription in trial?
     *
     * @var bool
     */
    private $inTrial = false;

    /**
     * Audit subscription changes.
     *
     * @var Array<Freemium\SubscriptionChange>
     */
    private $subscriptionChanges = [];

    /**
     * When this subscription should expire.
     *
     * @var DateTime|null
     */
    private $expireOn;

    /**
     * Transactions about current subscription charges.
     *
     * @var Array<Freemium\Transaction>
     */
    private $transactions = [];

    private int $rate;

    public function __construct(
        Subscribable $subscribable,
        SubscriptionPlan $plan
    ) {
        $this->subscribable = $subscribable;
        $this->setSubscriptionPlan($plan);
    }

    /**
     * Sets a SubscriptionPlan to current Subscription.
     *
     * This will
     * - calculate the rate for current Subscription
     * - set started date
     * - set paid through date
     * - create a SubscriptionChange
     *
     * @param SubscriptionPlan $plan
     * @return void
     */
    public function setSubscriptionPlan(SubscriptionPlan $plan): void
    {
        $this->originalPlan = $this->subscriptionPlan;
        $this->subscriptionPlan = $plan;
        $this->rate = $plan->getRate();
        $this->startedOn = new DateTime('today');

        if ($this->isPaid() && $this->subscribable->getBillingKey() === null) {
            throw new DomainException('Can not create paid subscription without a credit card.');
        }

        $this->applyPaidThrough();
        $this->createSubscriptionChange();
    }

    private function applyPaidThrough(): void
    {
        $notPaidSubscription = new PaidThrough\NotPaidSubscriptionCalculator($this);
        $newPaidSubscription = new PaidThrough\NewPaidSubscriptionCalculator($this);
        $creditRemainingValue = new PaidThrough\CreditRemainingValueCalculator($this);
        $default = new PaidThrough\DefaultCalculator($this);

        $notPaidSubscription->setSuccessor($newPaidSubscription);
        $newPaidSubscription->setSuccessor($creditRemainingValue);
        $creditRemainingValue->setSuccessor($default);

        $paidThrough = $notPaidSubscription->calculate();

        $this->paidThrough = $paidThrough->getDate();
        $this->expireOn = $paidThrough->getExpireOn() ?: $this->expireOn;
        $this->inTrial = $paidThrough->isInTrial();
    }

    private function createSubscriptionChange(): void
    {
        $change = new SubscriptionChange(
            $this,
            $this->getSubscriptionReason(),
            $this->originalPlan
        );

        $this->subscriptionChanges[] = $change;
    }

    private function getSubscriptionReason(): int
    {
        if (null === $this->originalPlan) {
            return SubscriptionChangeReason::REASON_NEW; # Fresh subscription.
        }

        if ($this->originalPlan->getRate() > $this->subscriptionPlan->getRate()) {
            return $this->isExpired()
                ? SubscriptionChangeReason::REASON_EXPIRE # Even Free plan may expire after a certain amount of time.
                : SubscriptionChangeReason::REASON_DOWNGRADE;
        }

        return SubscriptionChangeReason::REASON_UPGRADE;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    /**
     * {@inheritdoc}
     */
    public function rate(?DateTime $date = null): int
    {
        $date = $date ?: new DateTime('today');

        $value =  $this->subscriptionPlan->rate();
        if ($coupon = $this->getCoupon($date)) {
            $value = $coupon->getDiscount($value);
        }

        return $value;
    }

    /**
     * Applies a Coupon to current Subscription.
     *
     * @param Coupon $coupon
     * @return bool
     */
    public function applyCoupon(Coupon $coupon): bool
    {
        if ($coupon->appliesToPlan($this->getSubscriptionPlan())) {
            $couponRedemption = new CouponRedemption($this, $coupon);
            $this->couponRedemptions[] = $couponRedemption;

            return true;
        }

        return false;
    }

    /**
     * Gets best active coupon for a specific date.
     *
     * @param DateTime $date
     *
     * @return Coupon|null
     */
    public function getCoupon(DateTime $date = null): ?Coupon
    {
        $date = $date ?: new DateTime('today');

        if ($redemption = $this->getCouponRedemption($date)) {
            return $redemption->getCoupon();
        }

        return null;
    }

    /**
     * Gets best active coupon redemption for a specific date.
     *
     * @param DateTime $date
     *
     * @return CouponRedemption
     */
    public function getCouponRedemption(DateTime $date = null): ?CouponRedemption
    {
        $date = $date ?: new DateTime('today');
        if (empty($this->couponRedemptions)) {
            return null;
        }

        $active_redemptions = array_filter($this->couponRedemptions, function ($c) use ($date) {
            return $c->isActive($date);
        });

        $rate = $this->getSubscriptionPlan()->getRate();
        usort($active_redemptions, function ($a, $b) use ($rate) {
            $aDiscount = $a->getCoupon()->getDiscount($rate);
            $bDiscount = $b->getCoupon()->getDiscount($rate);

            return ($aDiscount < $bDiscount) ? -1 : 1;
        });

        return reset($active_redemptions) ?: null;
    }

    /**
     * Returns the money amount of the time between now and paidThrough.
     * Will optionally interpret the time according to a certain subscription plan.
     *
     * @param SubscriptionPlan $plan
     * @return int
     */
    public function remainingAmount(SubscriptionPlan $plan = null): int
    {
        if (null === $plan) {
            $plan = $this->subscriptionPlan;
        }

        return $this->getDailyRate(null, $plan) * $this->getRemainingDays();
    }

    /**
     * Gets the remaining days for the next payment cycle.
     * A negative number doesnt  mean that subscription has
     * expired. Maybe it is in grace.
     *
     * @return int
     */
    public function getRemainingDays(): int
    {
        $interval = (new DateTime('today'))->diff($this->getPaidThrough());

        return $interval->invert == 1 ? (-1 * $interval->days) : $interval->days;
    }

    /**
     * Returns remaining days of grace.
     * if under grace through today, returns zero
     *
     * @return int
     */
    public function getRemainingDaysOfGrace(): int
    {
        if (null == $this->expireOn) {
            return 0;
        }

        return (int) ($this->expireOn->diff(new DateTime('today'))->days);
    }

    /**
     * Checks if current Subscription is in grace.
     *
     * @return bool
     */
    public function isInGrace(): bool
    {
        return $this->getRemainingDaysOfGrace() > 0;
    }

    /**
     * Sets the Subscription to expire after applying the grace period.
     *
     * If paid through date is in future then grace days will apply to that
     * date.
     *
     * This will not run in Subscriptions that already have an expired date.
     *
     * @return void
     */
    public function expireAfterGrace(): void
    {
        if (null === $this->expireOn) {
            $max = max([new DateTime('today'), $this->getPaidThrough()]);
            $this->expireOn = $max->modify(Freemium::$daysGrace . ' days');
        }
    }

    /**
     * Expire a Subscription.
     *
     * This will
     * - set expiration date to today
     * - set current subscription plan to expire plan if any.
     * - destroy credit card data to local and remote systems.
     *
     * @return void
     */
    public function expireNow(): void
    {
        $this->expireOn = new DateTime('today');
        if (Freemium::getExpiredPlan()) {
            $this->setSubscriptionPlan(Freemium::getExpiredPlan());
        }
    }

    /**
     * Checks if current Subscription has been expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (null === $this->expireOn) {
            return false;
        }

        return $this->expireOn >= $this->paidThrough
            && $this->expireOn <= new DateTime('today');
    }

    /**
     * Current Subscription received a succesful payment.
     *
     * @return void
     */
    public function receivePayment(): void
    {
        $this->expireOn = null;
        $this->inTrial = false;
        $relative_format = $this->getSubscriptionPlan()->getCycleRelativeFormat();
        $this->paidThrough->modify($relative_format);
    }

    /**
     * Checks if subscription is in trial period.
     *
     * @return bool
     */
    public function isInTrial(): bool
    {
        return $this->inTrial;
    }

    /**
     * Get subscribable.
     *
     * @return Subscribable
     */
    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }

    /**
     * Get subscription plan.
     *
     * @return SubscriptionPlan
     */
    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    /**
     * Get started on.
     *
     * @return DateTime
     */
    public function getStartedOn(): DateTime
    {
        return $this->startedOn;
    }

    /**
     * Get paid through.
     *
     * @return DateTime|null
     */
    public function getPaidThrough(): ?DateTime
    {
        return $this->paidThrough;
    }

    /**
     * Get subscription changes collection.
     *
     * @return array<SubscriptionChange>
     */
    public function getSubscriptionChanges(): array
    {
        return $this->subscriptionChanges;
    }

    /**
     * Get coupon redemptions.
     *
     * @return array<couponRedemption>
     */
    public function getCouponRedemptions(): array
    {
        return $this->couponRedemptions;
    }

    public function createTransaction(Response $response): Transaction
    {
        $trx = new Transaction($response, $this->rate());
        $this->transactions[] = $trx;
        $this->lastTransactionAt = new DateTime();

        return $trx;
    }

    /**
     * Get last transaction date for this subscription.
     *
     * @return DateTime|null
     */
    public function getLastTransactionAt(): ?DateTime
    {
        return $this->lastTransactionAt;
    }

    /**
     * Get transactions.
     *
     * @return array<Transaction>.
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * Get expire on.
     *
     * @return DateTime|null
     */
    public function getExpireOn(): ?DateTime
    {
        return $this->expireOn;
    }

    public function getOriginalPlan(): ?SubscriptionPlan
    {
        return $this->originalPlan;
    }
}
