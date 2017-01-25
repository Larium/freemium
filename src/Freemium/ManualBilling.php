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
     */
    private $subscription;

    public function __construct($subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * The amount to charge for this cycle.
     *
     * Available options are:
     *  date A DateTime object to check available coupons for subscription
     *  plan A Freemium\SubscriptionPlan to get the rate from.
     *
     * @param array $options
     * @return float|int
     */
    public function getInstallmentAmount(array $options = array())
    {
        return $this->subscription->rate($options);
    }

    /**
     * Charge current subscription
     *
     * @return Freemiun\Transaction
     */
    public function charge()
    {
        $response = $this->subscription->gateway()->charge(
            $this->subscription->rate(),
            $this->subscription->getBillingKey(),
            $this->getInstallmentAmount()
        );

        $transaction = $this->subscription->createTransaction($response);

        if ($transaction->getSuccess()) {
            $this->subscription->receivePayment($transaction);
        } elseif (!$this->subscription->isInGrace()) {
            $this->subscription->expireAfterGrace($transaction);
        }

        return $transaction;
    }

    /**
     * Run billing cycle for the given subscriptions.
     *
     * @param array $subscriptions
     * @return void
     */
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
