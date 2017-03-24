<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;
use DomainException;
use SplObjectStorage;
use AktiveMerchant\Billing\Response;
use AktiveMerchant\Billing\CreditCard;
use Freemium\Gateways\GatewayInterface;

class Subscription implements RateInterface
{
    use Rate;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var Freemium\SubscribableInterface
     */
    private $subscribable;

    /**
     * Which service plan this subscription is for.
     * Affects how payment is interpreted.
     *
     * @var Freemium\SubscriptionPlan
     */
    private $subscription_plan;

    /**
     * The previous subsciption plan when subscription plan is changed.
     *
     * @var Freemium\SubscriptionPlan
     */
    private $original_plan;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     *
     * @var DateTime
     */
    private $paid_through;

    /**
     * When subscription started?
     *
     * @var DateTime
     */
    private $started_on;

    /**
     * When the last gateway transaction was for this account?
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTime|null
     */
    private $last_transaction_at;

    /**
     * @var Array<Freemium\CouponRedemption>
     */
    private $coupon_redemptions = [];

    /**
     * Is subscription in trial?
     *
     * @var bool
     */
    private $in_trial = false;

    /**
     * Audit subscription changes.
     *
     * @var Array<Freemium\SubscriptionChange>
     */
    private $subscription_changes = [];

    /**
     * When this subscription should expire.
     *
     * @var DateTime
     */
    private $expire_on;

    /**
     * Transactions about current subscription charges.
     *
     * @var Array<Freemium\Transaction>
     */
    private $transactions = [];

    /**
     * Observers for handling state changes for Subscription.
     *
     * @var array
     */
    private $observers = [];

    public function __construct(
        SubscribableInterface $subscribable,
        SubscriptionPlanInterface $plan
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
    public function setSubscriptionPlan(SubscriptionPlanInterface $plan) : void
    {
        $this->original_plan = $this->subscription_plan;
        $this->subscription_plan = $plan;
        $this->rate = $plan->getRate();
        $this->started_on = new DateTime('today');

        if ($this->isPaid() && $this->subscribable->getBillingKey() === null) {
            throw new DomainException('Can not create paid subscription without a credit card.');
        }

        $this->applyPaidThrough();
        $this->createSubscriptionChange();
    }

    private function applyPaidThrough() : void
    {
        if ($this->isPaid()) {
            if (null === $this->original_plan) { # Indicates a new Subscription
                # paid + new subscription = in free trial
                $this->paid_through = (new DateTime('today'))->modify(Freemium::$days_free_trial.' days');
                $this->in_trial = true;
            } elseif (!$this->in_trial
                && $this->original_plan
                && $this->original_plan->isPaid()
            ) {
                # paid + not in trial + not new subscription + original sub was paid
                # then calculate and credit for remaining value
                $amount = $this->remainingAmount($this->original_plan);
                $this->paid_through = new DateTime('today');
                $this->credit($amount);
            } else {
                # otherwise payment is due today
                $this->paid_through = new DateTime('today');
                $this->in_trial = false;
            }
        } else {
            # free plans don't pay
            $this->paid_through = null;
        }
    }

    private function createSubscriptionChange() : void
    {
        $reason = $this->getSubscriptionReason();
        $change = new SubscriptionChange($this, $reason, $this->original_plan);

        $this->subscription_changes[] = $change;
    }

    private function getSubscriptionReason() : int
    {
        if (null === $this->original_plan) {
            return SubscriptionChangeInterface::REASON_NEW; # Fresh subscription.
        }

        if ($this->original_plan->getRate() > $this->subscription_plan->getRate()) {
            return $this->isExpired()
                ? SubscriptionChangeInterface::REASON_EXPIRE # Even Free plan may expire after a certain amount of time.
                : SubscriptionChangeInterface::REASON_DOWNGRADE;
        }

        return SubscriptionChangeInterface::REASON_UPGRADE;
    }

    /**
     * {@inheritdoc}
     */
    public function rate(
        DateTime $date = null,
        SubscriptionPlanInterface $plan = null
    ) : int {
        $date = $date ?: new DateTime('today');
        $plan = $plan ?: $this->subscription_plan;

        $value = $plan->rate();
        if ($this->getCoupon($date)) {
            $value = $this->getCoupon($date)->getDiscount($value);
        }

        return $value;
    }

    /**
     * Applies a Coupon to current Subscription.
     *
     * @param Coupon $coupon
     * @return bool
     */
    public function applyCoupon(Coupon $coupon) : bool
    {
        if ($coupon->appliesToPlan($this->getSubscriptionPlan())) {
            $couponRedemption = new CouponRedemption($this, $coupon);
            $this->coupon_redemptions[] = $couponRedemption;

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
    public function getCoupon(DateTime $date = null) : ?Coupon
    {
        $date = $date ?: new DateTime('today');

        if ($redemption = $this->getCouponRedemption()) {
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
    public function getCouponRedemption(DateTime $date = null) : ?CouponRedemption
    {
        $date = $date ?: new DateTime('today');
        if (empty($this->coupon_redemptions)) {
            return null;
        }

        $active_redemptions = array_filter($this->coupon_redemptions, function ($c) use ($date) {
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
     * Returns the money amount of the time between now and paid_through.
     * Will optionally interpret the time according to a certain subscription plan.
     *
     * @param SubscriptionPlanInterface $plan
     * @return int
     */
    public function remainingAmount(SubscriptionPlanInterface $plan = null) : int
    {
        if (null === $plan) {
            $plan = $this->subscription_plan;
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
    public function getRemainingDays() : int
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
    public function getRemainingDaysOfGrace() : int
    {
        if (null == $this->expire_on) {
            return 0;
        }

        return (int) ($this->expire_on->diff(new DateTime('today'))->days);
    }

    /**
     * Checks if current Subscription is in grace.
     *
     * @return bool
     */
    public function isInGrace() : bool
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
    public function expireAfterGrace() : void
    {
        if (null === $this->expire_on) {
            $max = max([new DateTime('today'), $this->getPaidThrough()]);
            $this->expire_on = $max->modify(Freemium::$days_grace . ' days');
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
    public function expireNow() : void
    {
        $this->expire_on = new DateTime('today');
        if (Freemium::getExpiredPlan()) {
            $this->setSubscriptionPlan(Freemium::getExpiredPlan());
        }
    }

    /**
     * Checks if current Subscription has been expired.
     *
     * @return bool
     */
    public function isExpired() : bool
    {
        if (null === $this->expire_on) {
            return false;
        }

        return $this->expire_on >= $this->paid_through
            && $this->expire_on <= new DateTime('today');
    }

    /**
     * Current Subscription received a succesful payment.
     *
     * @param Freemium\Transaction $transaction
     * @return void
     */
    public function receivePayment(Transaction $transaction) : void
    {
        $this->credit($transaction->getAmount());
    }

    private function credit(int $amount) : void
    {
        if ($amount % $this->rate() == 0) {
            # Given amount match the rate of subscription plan.
            $cycles = round($amount / $this->rate());
            $relative_format = $this->getSubscriptionPlan()->getCycleRelativeFormat($cycles);
            $this->paid_through->modify($relative_format);
        } else {
            # Given amount does not match the rate of subscription plan so this
            # could be credit from downgrading a paid subscription plan.
            # So give back days as credit.
            $days = ceil($amount / $this->getDailyRate());
            $this->paid_through->modify("$days days");
        }

        $this->expire_on = null;
        $this->in_trial = false;
    }

    /**
     * Checks if subscription is in trial period.
     *
     * @return bool
     */
    public function isInTrial() : bool
    {
        return $this->in_trial;
    }

    /**
     * Get subscribable.
     *
     * @return SubscribableInterface
     */
    public function getSubscribable() : SubscribableInterface
    {
        return $this->subscribable;
    }

    /**
     * Get subscription plan.
     *
     * @return SubscriptionPlan
     */
    public function getSubscriptionPlan() : SubscriptionPlan
    {
        return $this->subscription_plan;
    }

    /**
     * Get started on.
     *
     * @return DateTime
     */
    public function getStartedOn() : DateTime
    {
        return $this->started_on;
    }

    /**
     * Get paid through.
     *
     * @return DateTime|null
     */
    public function getPaidThrough() : ?DateTime
    {
        return $this->paid_through;
    }

    /**
     * Get subscription changes collection.
     *
     * @return array<SubscriptionChange>
     */
    public function getSubscriptionChanges() : array
    {
        return $this->subscription_changes;
    }

    /**
     * Get coupon redemptions.
     *
     * @return array<couponRedemption>
     */
    public function getCouponRedemptions() : array
    {
        return $this->coupon_redemptions;
    }

    public function createTransaction(Response $response) : Transaction
    {
        $trx = new Transaction($response, $this->rate());
        $this->transactions[] = $trx;
        $this->last_transaction_at = new DateTime();

        return $trx;
    }

    /**
     * Get last transaction date for this subscription.
     *
     * @return DateTime|null
     */
    public function getLastTransactionAt() : ?DateTime
    {
        return $this->last_transaction_at;
    }

    /**
     * Get transactions.
     *
     * @return array<Transaction>.
     */
    public function getTransactions() : array
    {
        return $this->transactions;
    }

    /**
     * Get expire on.
     *
     * @return DateTime
     */
    public function getExpireOn() : DateTime
    {
        return $this->expire_on;
    }
}
