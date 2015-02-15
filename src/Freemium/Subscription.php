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

    public function setSubscriptionPlan(SubscriptionPlan $plan)
    {
        $this->original_plan = $this->subscription_plan;

        $this->subscription_plan = $plan;

        $this->rate = $plan->getRate();

        $this->started_on = new DateTime('today');

        $this->apply_paid_through();

        $reason = null === $this->original_plan ? 'new' :
            ($this->original_plan->getRate() > $plan->getRate() ?
            ($this->isExpired() ? 'expiration' : 'downgrade') : 'upgrade');
        $change = new SubscriptionChange();
        $params = [
            'reason' => $reason,
            'new_subscription_plan' => $plan,
            'new_rate' => $plan->getRate(),
            'original_subscription_plan' => $this->original_plan,
            'original_rate' => null !== $this->original_plan ? $this->original_plan->getRate() : 0,
            'subscription' => $this,
            'created_at' => new DateTime()
        ];

        $change->setProperties($params);

        $this->subscription_changes[] = $change;
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

            } else {
                # otherwise payment is due today
                $this->paid_through = new DateTime('today');
                $this->in_trial = false;
            }
        } else {
            $this->paid_through = null;
        }
    }

    public function storeCreditCardOffsite()
    {
        if ($this->credit_card
            && $this->credit_card_changed
            && $this->credit_card->isValid()
        ) {
            $gateway = Freemium::getGateway();

            $response = $gateway->store($this->credit_card);

            $this->billing_key = $response->billingid;
        }
    }

    public function setCreditCard(CreditCard $credit_card)
    {
        $this->credit_card = $credit_card;
        $this->credit_card_changed = true;
    }

    public function isExpired()
    {
        return $this->expire_on and $this->expire_on <= new DateTime('today');
    }
}
