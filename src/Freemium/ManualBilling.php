<?php

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
     * @param DateTime $date The date to check available coupons for subscription.
     * @param Freemiun\SubscriptionPlanInterface $plan A plan to get the rate from.
     * @return float|int
     */
    public function getInstallmentAmount(
        DateTime $date = null,
        SubscripionPlanInterface $plan = null
    ) {
        return $this->subscription->rate($date, $plan);
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
