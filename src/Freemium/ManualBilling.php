<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

class ManualBilling
{
    /**
     * The Subscription to charge
     *
     * @var Subscription
     */
    private $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * The amount to charge for this cycle.
     *
     * @param DateTime $date The date to check available coupons for subscription.
     * @param Freemiun\SubscriptionPlanInterface $plan A plan to get the rate from.
     * @return int
     */
    public function getInstallmentAmount(
        DateTime $date = null,
        SubscripionPlanInterface $plan = null
    ) : int {
        return $this->subscription->rate($date, $plan);
    }

    /**
     * Charge current subscription
     *
     * @return Transaction
     */
    public function charge() : Transaction
    {
        $response = $this->subscription->gateway()->charge(
            $this->subscription->rate(),
            $this->subscription->getBillingKey(),
            $this->getInstallmentAmount()
        );

        $transaction = $this->subscription->createTransaction($response);

        if ($transaction->isSuccess()) {
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
    public static function runBilling(array $subscriptions) : void
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
