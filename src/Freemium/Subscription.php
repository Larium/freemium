<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\CreditCard;
use Doctrine\Common\Collections\ArrayCollection;
use SplSubject;
use SplObserver;
use SplObjectStorage;

class Subscription extends AbstractEntity implements RateInterface, SplSubject
{
    use Rate;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var SubscribableInterface
     * @access protected
     */
    protected $subscribable;

    /**
     * Which service plan this subscription is for.
     * Affects how payment is interpreted.
     *
     * @var SubscriptionPlan
     * @access protected
     */
    protected $subscription_plan;

    /**
     * The previous subsciption plan when subscription plan is changed.
     *
     * @var SubscriptionPlan
     * @access protected
     */
    protected $original_plan;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     *
     * @var DateTime
     * @access protected
     */
    protected $paid_through;

    /**
     * When subscription started?
     *
     * @var DateTime
     * @access protected
     */
    protected $started_on;

    /**
     * The id for this user in the remote billing gateway.
     * May not exist if user is on a free plan.
     *
     * @var string
     * @access protected
     */
    protected $billing_key;

    /**
     * When the last gateway transaction was for this account.
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTime
     * @access protected
     */
    protected $last_transaction_at;

    /**
     * @var ArrayCollection<CouponRedemption>
     * @access protected
     */
    protected $coupon_redemptions;

    /**
     * Is subscription in trial?
     *
     * @var boolean
     * @access protected
     */
    protected $in_trial = false;

    /**
     * The credit card used for paid subscriptions.
     *
     * @var AktiveMerchant\Billing\CreditCard
     * @access protected
     */
    protected $credit_card;

    /**
     * Whether a credit card changed or not.
     *
     * @var boolen
     * @access protected
     */
    protected $credit_card_changed;

    /**
     * Audit subscription changes.
     *
     * @var ArrayCollection<SubscriptionChange>
     * @access protected
     */
    protected $subscription_changes;

    /**
     * When this subscription should expire.
     *
     * @var DateTime
     * @access protected
     */
    protected $expire_on;

    /**
     * Transactions about current subscription charges.
     *
     * @var ArrayCollection<Transaction>
     * @access protected
     */
    protected $transactions;

    protected $observers;

    public function __construct()
    {
        $this->subscription_changes = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->coupon_redemptions = new ArrayCollection();
        $this->observers = new SplObjectStorage();
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

    public function setSubscriptionPlan(SubscriptionPlan $plan)
    {
        $this->original_plan = $this->subscription_plan;

        $this->subscription_plan = $plan;

        $this->rate = $plan->getRate();

        $this->started_on = new DateTime('today');

        $this->apply_paid_through();

        $this->create_subscription_change();
    }

    protected function apply_paid_through()
    {
        if ($this->isPaid()) {
            if (null === $this->original_plan) { #Indicates new Subscription
                # paid + new subscription = in free trial
                $this->paid_through = (new DateTime('today'))->modify(Freemium::$days_free_trial.' days');
                $this->in_trial = true;
            } elseif (!$this->in_trial && $this->original_plan && $this->original_plan->isPaid()) {
                # paid + not in trial + not new subscription + original sub was paid = calculate and credit for remaining value
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

    protected function create_subscription_change()
    {
        if (null === $this->original_plan) {
            $reason = 'new'; # Fresh subscription.
        } else {
            if ($this->original_plan->getRate() > $this->subscription_plan->getRate()) {
                if ($this->isExpired()) {
                    # Even Free plan may expire after a certain amount of time.
                    $reason = 'expiration';
                } else {
                    $reason = 'downgrade';
                }
            } else {
                $reason = 'upgrade';
            }
        }

        $change = new SubscriptionChange();
        $params = [
            'reason' => $reason,
            'new_subscription_plan' => $this->subscription_plan,
            'new_rate' => $this->subscription_plan->getRate(),
            'original_subscription_plan' => $this->original_plan,
            'original_rate' => null !== $this->original_plan ? $this->original_plan->getRate() : 0,
            'subscription' => $this,
            'created_at' => new DateTime()
        ];

        $change->setProperties($params);

        $this->subscription_changes->add($change);
    }

    public function storeCreditCardOffsite()
    {
        if (   $this->credit_card
            && $this->credit_card_changed
            && $this->credit_card->isValid()
        ) {
            $response = $this->gateway()->store($this->credit_card);

            $this->billing_key = $response->billingid;
        }
    }

    protected function discard_credit_card_unless_paid()
    {
        if (!$this->store_credit_card) {
            $this->destroy_credit_card();
        }
    }

    protected function destroy_credit_card()
    {
        $this->credit_card = null;
        $this->cancel_in_remote_system();
    }

    protected function cancel_in_remote_system()
    {
        if ($this->billing_key) {
            $gateway = $this->gateway();
            $response = $gateway->unstore($this->billing_key);

            $this->billing_key = null;
        }
    }

    # Rate

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

        $value = $plan->getRate();
        if ($this->getCoupon($date)) {
            $value = $this->getCoupon($date)->getDiscount($value);
        }
        return $value;
    }

    /**
     * Allow for more complex logic to decide if a card should be stored.
     *
     * @access protected
     * @return boolean
     */
    protected function can_store_credit_card()
    {
        return $this->isPaid();
    }

    # Coupon Redemption

    public function applyCoupon(Coupon $coupon)
    {
        if ($coupon->appliesToPlan($this->getSubscriptionPlan())) {
            $couponRedemption = new CouponRedemption();
            $couponRedemption->setSubscription($this);
            $couponRedemption->setCoupon($coupon);
            $this->coupon_redemptions->add($couponRedemption);

            return true;
        }

        return false;
    }

    /**
     * Gets best active coupon for a specific date.
     *
     * @param DateTime $date
     * @access public
     * @return Freemium\Coupon|null
     */
    public function getCoupon(DateTime $date = null)
    {
        $date = $date ?: new DateTime('today');

        if (   $this->getCouponRedemption()
            && $this->getCouponRedemption()->getCoupon()
        ) {
            return $this->getCouponRedemption()->getCoupon();
        }
    }

    /**
     * Gets best active coupon redemption for a specific date.
     *
     * @param DateTime $date
     * @access public
     * @return Freemium\CouponRedemption
     */
    public function getCouponRedemption(DateTime $date = null)
    {
        $date = $date ?: new DateTime('today');
        if ($this->coupon_redemptions->isEmpty()) {
            return null;
        }

        $active_coupons = $this->coupon_redemptions->filter(function($c) use ($date) {
            return $c->isActive($date);
        });

        if ($active_coupons->isEmpty()) {
            return null;
        }

        $active_coupons = $active_coupons->toArray();
        usort($active_coupons, function($a, $b) {
            ($a->getCoupon()->getDiscountPercentage() < $b->getCoupon()->getDiscountPercentage()) ? -1 : 1;
        });

        return end($active_coupons);
    }

    # Remaining Time

    /**
     * Returns the money amount of the time between now and paid_through.
     * Will optionally interpret the time according to a certain subscription plan.
     *
     * @param SubscriptionPlan $plan
     * @access public
     * @return integer|float
     */
    public function remainingAmount(SubscriptionPlan $plan = null)
    {
        if (null === $plan) {
            $plan = $this->subscription_plan;
        }

        return $this->getDailyRate(['plan' => $plan]) * $this->getRemainingDays();
    }

    /**
     * Gets the remaining days for the next payment cycle.
     *
     * @access public
     * @return integer A negative number doesnt  mean that subscription has
     *                 expired. Maybe it is in grace.
     */
    public function getRemainingDays()
    {
        $interval = (new DateTime('today'))->diff($this->getPaidThrough());
        return $interval->invert == 1 ? (-1 * $interval->days) : $interval->days;
    }

    # Grace Period


    /**
     * Returns remaining days of grace.
     * if under grace through today, returns zero
     *
     * @access public
     * @return integer
     */
    public function getRemainingDaysOfGrace()
    {
        if (null == $this->expire_on) {
            return 0;
        }
        return (int) ($this->expire_on->diff(new DateTime('today'))->days);
    }


    public function isInGrace()
    {
        return $this->getRemainingDays() < 0 && !$this->isExpired();
    }

    # Expiration

    public function expireAfterGrace(Transaction $transaction = null)
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

    public function expireNow()
    {
        $this->expire_on = new DateTime('today');
        # TODO: set the expired subscription plan if any, ex. free plan.
        $this->destroy_credit_card();
        $this->notify();
    }

    public function isExpired()
    {
        return $this->expire_on && $this->expire_on <= new DateTime('today');
    }

    # Receiving More Money

    public function receivePayment(Transaction $transaction)
    {
        $this->credit($transaction->getAmount());

        $transaction->setMessage(sprintf('now paid through %s', $this->getPaidThrough()->format('Y-m-d H:i:s')));

        $this->notify();
        # TODO: send invoice via email.
    }

    protected function credit($amount)
    {
        if ($amount % $this->rate == 0) {
            $months = round($amount / $this->rate);
            $this->paid_through->modify("$months months");
        } else {
            $days = ceil($amount / $this->getDailyRate());
            $this->paid_through->modify("$days days");
        }


        $this->expire_on = null;
        $this->in_trial = false;
    }

    public function addTransaction(Transaction $transaction)
    {
        $transaction->setSubscription($this);
        $this->transactions[] = $transaction;
    }


    # SplSubject

    public function attach(SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    public function detach(SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}
