<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;
use DomainException;
use Freemium\Freemium;
use AktiveMerchant\Billing\Response;

use function bccomp;

class Subscription
{
    public const TOKEN_PREFIX = 'sub_';

    private readonly string $token;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var Subscribable
     */
    private Subscribable $subscribable;

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
    private ?DateTime $paidThrough = null;

    /**
     * When subscription started?
     *
     * @var DateTime
     */
    private DateTime $startedOn;

    /**
     * When the last gateway transaction was for this account?
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTime|null
     */
    private ?DateTime $lastTransactionAt = null;

    /**
     * @var CouponRedemption[]
     */
    private array $couponRedemptions = [];

    /**
     * Is subscription in trial?
     *
     * @var bool
     */
    private bool $inTrial = false;

    /**
     * Audit subscription changes.
     *
     * @var SubscriptionChange[]
     */
    private array $subscriptionChanges = [];

    /**
     * When this subscription should expire.
     *
     * @var DateTime|null
     */
    private $expireOn;

    /**
     * Transactions about current subscription charges.
     *
     * @var Transaction[]
     */
    private array $transactions = [];

    private Money $rate;

    private SubscriptionStatus $status = SubscriptionStatus::ACTIVE;

    public function __construct(
        string $token,
        Subscribable $subscribable,
        /** Which service plan this subscription is for. Affects how payment is interpreted.*/
        private SubscriptionPlan $subscriptionPlan,
    ) {
        $this->token = $token;
        $this->subscribable = $subscribable;
        $this->calculateForPlan($subscriptionPlan);
    }

    public function getToken(): string
    {
        return $this->token;
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
        if ($this->subscriptionPlan !== null
            && $this->subscriptionPlan->getName() === $plan->getName()
        ) {
            return;
        }

        $this->originalPlan = $this->subscriptionPlan;
        $this->subscriptionPlan = $plan;
        $this->calculateForPlan($plan);
    }

    private function calculateForPlan(SubscriptionPlan $plan): void
    {
        $this->rate = $plan->getRate();
        $this->startedOn = new DateTime('today');

        if ($this->isPaid() && $this->subscribable->getBillingKey() === null) {
            throw new DomainException('Can not create paid subscription without a billing key.');
        }

        $this->applyPaidThrough(new RateCalculator());
        $change = new SubscriptionChange(
            $this,
            $this->getSubscriptionReason(),
            $this->originalPlan
        );

        $this->subscriptionChanges[] = $change;
    }

    private function applyPaidThrough(RateCalculator $rateCalculator): void
    {
        $notPaidSubscription = new PaidThrough\NotPaidSubscriptionCalculator($this);
        $newPaidSubscription = new PaidThrough\NewPaidSubscriptionCalculator($this);
        $creditRemainingValue = new PaidThrough\CreditRemainingValueCalculator($this, $rateCalculator);
        $default = new PaidThrough\DefaultCalculator($this);

        $notPaidSubscription->setSuccessor($newPaidSubscription);
        $newPaidSubscription->setSuccessor($creditRemainingValue);
        $creditRemainingValue->setSuccessor($default);

        $state = $notPaidSubscription->calculate();

        $this->paidThrough = $state->getPaidThrough();
        $this->expireOn = $state->getExpireOn() ?: $this->expireOn;
        $this->inTrial = $state->isInTrial();
    }

    private function getSubscriptionReason(): SubscriptionChangeReason
    {
        if ($this->originalPlan === null) {
            return SubscriptionChangeReason::REASON_NEW; # Fresh subscription.
        }

        if ($this->originalPlan->getRate()->greater($this->subscriptionPlan->getRate())) {
            return $this->isExpired()
                ? SubscriptionChangeReason::REASON_EXPIRE # Even Free plan may expire after a certain amount of time.
                : SubscriptionChangeReason::REASON_DOWNGRADE;
        }

        return SubscriptionChangeReason::REASON_UPGRADE;
    }

    public function getRate(): Money
    {
        return $this->rate;
    }

    public function isPaid(): bool
    {
        return $this->rate->greater(Money::zero($this->rate->getCurrency()));
    }

    /**
     * The amount to charge the gateway for one billing cycle (plan rate with coupon applied at cycle level).
     */
    public function billingAmount(?DateTime $date = null): Money
    {
        $date = $date ?: new DateTime('today');

        $value = $this->subscriptionPlan->getRate();
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
    public function applyCoupon(Coupon $coupon, string $redemptionToken): bool
    {
        if ($coupon->appliesToPlan($this->getSubscriptionPlan())) {
            $couponRedemption = new CouponRedemption($redemptionToken, $this, $coupon);
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

            return bccomp($aDiscount->getMinorAmount(), $bDiscount->getMinorAmount());
        });

        return reset($active_redemptions) ?: null;
    }

    /**
     * Returns the money amount of the time between now and paidThrough.
     *
     * @return Money Amount in minor units (may be negative if past due)
     */
    public function remainingAmount(): Money
    {
        $dailyRate = (new RateCalculator())->dailyRate($this->getSubscriptionPlan());

        return $dailyRate->multiply((string) $this->getRemainingDays());
    }

    /**
     * Gets the remaining days for the next payment cycle.
     * A negative number doesn' t  mean that subscription has
     * expired. Maybe it is in grace.
     *
     * @return int
     */
    public function getRemainingDays(): int
    {
        if ($this->getPaidThrough() === null) {
            return 0;
        }

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
        if ($this->expireOn === null) {
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
            $this->expireOn = (clone $max)->modify(Freemium::$daysGrace . ' days');
        }
    }

    /**
     * Expire a Subscription.
     *
     * This will
     * - set expiration date to today
     * - set status to PAST_DUE for the subscription
     *
     * @return void
     */
    public function expireNow(): void
    {
        $this->expireOn = new DateTime('today');
        $this->status = SubscriptionStatus::PAST_DUE;
    }

    /**
     * Checks if current Subscription has been expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if ($this->expireOn === null) {
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
        $this->status = SubscriptionStatus::ACTIVE;
        $relative_format = $this->getSubscriptionPlan()->getCycleRelativeFormat();
        $this->paidThrough ??= new DateTime('today');
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
     * @return SubscriptionChange[]
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

    public function createTransaction(string $transactionToken): Transaction
    {
        $trx = new Transaction($transactionToken, $this->billingAmount());
        $this->transactions[] = $trx;
        $this->lastTransactionAt = new DateTime();

        return $trx;
    }

    public function captureTransaction(Response $response): void
    {
        $trx = $this->transactions[count($this->transactions) - 1];
        $trx->capture($response);
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
     * @return Transaction[]
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
