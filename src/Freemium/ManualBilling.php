<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class ManualBilling
{
    /**
     * The Subscription to charge
     *
     * @var Freemium\Subscription
     * @access protected
     */
    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * The amount to charge for this cycle.
     *
     * Available options are:
     *  date A DateTime object to check available coupons for subscription
     *  plan A Freemium\SubscriptionPlan to get the rate.
     *
     * @param array $options
     * @access public
     * @return float|integer
     */
    public function getInstallmentAmount(array $options = array())
    {
        return $this->subscription->rate($options);
    }

    /**
     * Charge current subscription
     *
     * @access public
     * @return Transaction
     */
    public function charge()
    {
        $response = $this->subscription->gateway()->charge(
            $this->subscription->rate(),
            $this->subscription->getBillingKey(),
            $this->getInstallmentAmount()
        );

        $transaction = new Transaction();
        $transaction->setData([
            'billing_key' => $this->subscription->getBillingKey(),
            'amount' => $this->subscription->rate(),
            'success' => $response->success()
        ]);

        $this->subscription->addTransaction($transaction);
        $this->subscription->setLastTransactionAt(new DateTime('now'));

        if ($transaction->getSuccess()) {
            $this->subscription->receivePayment($transaction);
        } elseif (!$this->subscription->isInGrace()) {
            $this->subscription->expireAfterGrace($transaction);
        }

        return $transaction;
    }

    public static function runBilling(array $subscriptions)
    {
        foreach ($subscriptions as $sub) {
            $billing = new self($sub);
            $billing->charge();

            if ($sub->isExpired()) {
                $sub->expireNow();
            }
        }
    }
}
