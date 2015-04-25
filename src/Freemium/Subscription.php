<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\CreditCard;

class Subscription extends AbstractEntity
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
     * @var array<CouponRedemption>
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
     * @var array<SubscriptionChange>
     * @access protected
     */
    protected $subscription_changes = array();

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
     * @var array<Transaction>
     * @access protected
     */
    protected $transactions = array();

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
                $value = $this->remainingValue($this->original_plan);
                $this->paid_through = new DateTime('today');
                $this->credit($value);
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
        $reason = null === $this->original_plan ? 'new' :
            ($this->original_plan->getRate() > $this->subscription_plan->getRate() ?
            ($this->isExpired() ? 'expiration' : 'downgrade') : 'upgrade');
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

        $this->subscription_changes[] = $change;
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
            $gateway = Freemium::getGateway();
            $response = $gateway->unstore($this->billing_key);

            $this->billing_key = null;
        }
    }

    # Rate

    public function rate(array $options = array())
    {
        $options = array_merge([
            'date' => new DateTime('today'),
            'plan' => $this->subscription_plan], $options);

        if (isset($options['plan'])) {
            $value = $options['plan']->getRate();

            return $value;
        }
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

    public function setCoupon(Coupon $coupon)
    {
        $couponRedemption = new CouponRedemption();
        $couponRedemption->setSubscription($this);
        $couponRedemption->setCoupon($coupon);
        $this->coupon_redemptions[] = $couponRedemption;
    }

    public function getCouponRedemption(DateTime $date = null)
    {
        $date = $date ?: new DateTime('today');
        if (empty($this->coupon_redemptions)) {
            return null;
        }

        $active_coupons = array_filter($this->coupon_redemptions, function($c) use ($date) {
            return $c->isActive($date);
        });

        if (empty($active_coupons)) {
            return null;
        }

        usort($active_coupons, function($a, $b) {
            ($a->getCoupon()->getDiscountPercentage() < $b->getCoupon()->getDiscountPercentage()) ? -1 : 1;
        });

        return end($active_coupons);
    }

    # Remaining Time

    /**
     * returns the value of the time between now and paid_through.
     * will optionally interpret the time according to a certain subscription plan.
     *
     * @param SubscriptionPlan $plan
     * @access public
     * @return void
     */
    public function remainingValue(SubscriptionPlan $plan = null)
    {
        if (null === $plan) {
            $plan = $this->subscription_plan;
        }

        return $this->getDailyRate(['plan' => $plan]) * $this->getRemainingDays();
    }

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
        }
    }

    public function expireNow()
    {
        $this->expire_on = new DateTime('today');
        $this->destroy_credit_card();
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
}
