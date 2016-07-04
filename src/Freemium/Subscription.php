<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use SplSubject;
use SplObserver;
use LogicException;
use SplObjectStorage;
use AktiveMerchant\Billing\Response;
use AktiveMerchant\Billing\CreditCard;

class Subscription implements RateInterface, SplSubject
{
    use Rate;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var SubscribableInterface
     */
    protected $subscribable;

    /**
     * Which service plan this subscription is for.
     * Affects how payment is interpreted.
     *
     * @var SubscriptionPlan
     */
    protected $subscription_plan;

    /**
     * The previous subsciption plan when subscription plan is changed.
     *
     * @var SubscriptionPlan
     */
    protected $original_plan;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     *
     * @var DateTime
     */
    protected $paid_through;

    /**
     * When subscription started?
     *
     * @var DateTime
     */
    protected $started_on;

    /**
     * The id for this user in the remote billing gateway.
     * May not exist if user is on a free plan.
     *
     * @var string
     */
    protected $billing_key;

    /**
     * When the last gateway transaction was for this account?
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTime
     */
    protected $last_transaction_at;

    /**
     * @var Array<CouponRedemption>
     */
    protected $coupon_redemptions = [];

    /**
     * Is subscription in trial?
     *
     * @var boolean
     */
    protected $in_trial = false;

    /**
     * The credit card used for paid subscriptions.
     *
     * @var AktiveMerchant\Billing\CreditCard
     */
    protected $credit_card;

    /**
     * Whether a credit card changed or not.
     *
     * @var boolean
     */
    protected $credit_card_changed;

    /**
     * Audit subscription changes.
     *
     * @var Array<SubscriptionChange>
     */
    protected $subscription_changes = [];

    /**
     * When this subscription should expire.
     *
     * @var DateTime
     */
    protected $expire_on;

    /**
     * Transactions about current subscription charges.
     *
     * @var Array<Transaction>
     */
    protected $transactions = [];

    /**
     * Observers for handling state changes for Subscription.
     *
     * @var SplObjectStorage
     */
    protected $observers = [];

    public function __construct(SubscribableInterface $subscribable)
    {
        $this->subscribable = $subscribable;
    }

    public function gateway()
    {
        return Freemium::getGateway();
    }

    public function setCreditCard(CreditCard $credit_card)
    {
        $this->credit_card = $credit_card;
        $this->credit_card_changed = true;
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
    public function setSubscriptionPlan(SubscriptionPlanInterface $plan)
    {
        $this->original_plan = $this->subscription_plan;
        $this->subscription_plan = $plan;
        $this->rate = $plan->getRate();
        $this->started_on = new DateTime('today');

        $this->applyPaidThrough();
        $this->createSubscriptionChange();
    }

    protected function applyPaidThrough()
    {
        if ($this->isPaid()) {
            if (null === $this->original_plan) { #Indicates new Subscription
                # paid + new subscription = in free trial
                $this->paid_through = (new DateTime('today'))->modify(Freemium::$days_free_trial.' days');
                $this->in_trial = true;
            } elseif (!$this->in_trial && $this->original_plan && $this->original_plan->isPaid()) {
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

    protected function createSubscriptionChange()
    {
        $reason = $this->getSubscriptionReason();
        $change = new SubscriptionChange($this, $reason, $this->original_plan);

        $this->subscription_changes[] = $change;
    }

    private function getSubscriptionReason()
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

    public function storeCreditCardOffsite()
    {
        if ($this->credit_card
            && $this->credit_card_changed
            && $this->credit_card->isValid()
        ) {
            $response = $this->gateway()->store($this->credit_card);

            $this->billing_key = $response->billingid;

            $this->credit_card_changed = false;
        }
    }

    /*
    protected function discard_credit_card_unless_paid()
    {
        if (!$this->can_store_credit_card()) {
            $this->destroyCreditCard();
        }
    }
     */

    /**
     * Allow for more complex logic to decide if a card should be stored.
     *
     * @return boolean
    protected function can_store_credit_card()
    {
        return $this->isPaid();
    }
     */

    protected function destroyCreditCard()
    {
        $this->credit_card = null;
        $this->cancelInRemoteSystem();
    }

    protected function cancelInRemoteSystem()
    {
        if (null !== $this->billing_key) {
            $gateway = $this->gateway();
            $gateway->unstore($this->billing_key);

            $this->billing_key = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rate(array $options = array())
    {
        $date = isset($options['date']) ? $options['date'] : new DateTime('today');
        $plan = isset($options['plan']) ? $options['plan'] : $this->subscription_plan;
        if (null == $plan) {
            return null;
        }

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
    public function applyCoupon($coupon)
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
    public function getCoupon(DateTime $date = null)
    {
        $date = $date ?: new DateTime('today');

        if ($redemption = $this->getCouponRedemption()) {
            return $redemption->getCoupon();
        }
    }

    /**
     * Gets best active coupon redemption for a specific date.
     *
     * @param DateTime $date
     *
     * @return CouponRedemption
     */
    public function getCouponRedemption(DateTime $date = null)
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
            return ($a->getCoupon()->getDiscount($rate) < $b->getCoupon()->getDiscount($rate)) ? -1 : 1;
        });

        return reset($active_redemptions);
    }

    /**
     * Returns the money amount of the time between now and paid_through.
     * Will optionally interpret the time according to a certain subscription plan.
     *
     * @param SubscriptionPlanInterface $plan
     * @return integer|float
     */
    public function remainingAmount(SubscriptionPlanInterface $plan = null)
    {
        if (null === $plan) {
            $plan = $this->subscription_plan;
        }

        return $this->getDailyRate(['plan' => $plan]) * $this->getRemainingDays();
    }

    /**
     * Gets the remaining days for the next payment cycle.
     * A negative number doesnt  mean that subscription has
     * expired. Maybe it is in grace.
     *
     * @return int
     */
    public function getRemainingDays()
    {
        $interval = (new DateTime('today'))->diff($this->getPaidThrough());

        return $interval->invert == 1 ? (-1 * $interval->days) : $interval->days;
    }

    /**
     * Returns remaining days of grace.
     * if under grace through today, returns zero
     *
     * @return integer
     */
    public function getRemainingDaysOfGrace()
    {
        if (null == $this->expire_on) {
            return 0;
        }
        return (int) ($this->expire_on->diff(new DateTime('today'))->days);
    }


    /**
     * Checks if current Subscription is in grace.
     *
     * @return boolean
     */
    public function isInGrace()
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
     * @param Transaction $transaction
     * @return void
     */
    public function expireAfterGrace($transaction = null)
    {
        if (null === $this->expire_on) {
            $max = max([new DateTime('today'), $this->getPaidThrough()]);
            $this->expire_on = $max->modify(Freemium::$days_grace . ' days');
            if ($transaction) {
                $transaction->setMessage(sprintf('now set to expire on %s', $this->expire_on->format('Y-m-d H:i:s')));
            }
            $this->notify();
        }
    }

    /**
     * Expire a Subscription.
     *
     * This will
     * - set expiration date to today
     * - set current subscription plan to expire plan if any.
     * - destroy credit card data to local and remote systems.
     * - notify user for expiration.
     *
     * @return void
     */
    public function expireNow()
    {
        $this->expire_on = new DateTime('today');
        if (Freemium::getExpiredPlan()) {
            $this->setSubscriptionPlan(Freemium::getExpiredPlan());
        }
        $this->destroyCreditCard();
        $this->notify();
    }

    /**
     * Checks if current Subscription has been expired.
     *
     * @return bool
     */
    public function isExpired()
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
     * @param Transaction $transaction
     *
     * @return void
     */
    public function receivePayment($transaction)
    {
        $this->credit($transaction->getAmount());

        $transaction->setMessage(sprintf('now paid through %s', $this->getPaidThrough()->format('Y-m-d H:i:s')));

        $this->notify();
    }

    protected function credit($amount)
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
     * {@inheritdoc}
     */
    public function attach(SplObserver $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * {@inheritdoc}
     */
    public function detach(SplObserver $observer)
    {
        $this->observers = array_udiff($this->observers, array($observer), function ($a, $b) {
            return ($a === $b) ? 0 : 1;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getObservers()
    {
        return $this->observers;
    }

    public function isInTrial()
    {
        return $this->in_trial;
    }

    /**
     * Get subscribable.
     *
     * @return SubscribableInterface
     */
    public function getSubscribable()
    {
        return $this->subscribable;
    }

    /**
     * Get subscription plan.
     *
     * @return SubscriptionPlan
     */
    public function getSubscriptionPlan()
    {
        return $this->subscription_plan;
    }

    /**
     * Get started on.
     *
     * @return DateTime
     */
    public function getStartedOn()
    {
        return $this->started_on;
    }

    /**
     * Get paid through.
     *
     * @return DateTime
     */
    public function getPaidThrough()
    {
        return $this->paid_through;
    }

    /**
     * Get subscription changes collection.
     *
     * @return ArrayCollection<SubscriptionChange>
     */
    public function getSubscriptionChanges()
    {
        return $this->subscription_changes;
    }

    /**
     * Get billing key.
     *
     * @return string
     */
    public function getBillingKey()
    {
        return $this->billing_key;
    }

    /**
     * Get coupon redemptions.
     *
     * @return ArrayCollection<couponRedemption>
     */
    public function getCouponRedemptions()
    {
        return $this->coupon_redemptions;
    }

    public function createTransaction(Response $response)
    {
        $trx = new Transaction($this, $this->rate(), $this->getBillingKey());
        $trx->setSuccess($response->success());
        $trx->setMessage($response->message());
        $this->transactions[] = $trx;
        $this->last_transaction_at = new DateTime();

        return $trx;
    }

    /**
     * Get last transaction date for this subscription.
     *
     * @return DateTime
     */
    public function getLastTransactionAt()
    {
        return $this->last_transaction_at;
    }

    /**
     * Get transactions.
     *
     * @return ArrayCollection<Transaction>.
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Get expire on.
     *
     * @return DateTime
     */
    public function getExpireOn()
    {
        return $this->expire_on;
    }
}
