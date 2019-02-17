<?php

declare(strict_types=1);

namespace Freemium\Command\ChangePlan;

use Freemium\Subscription;
use Freemium\SubscriptionPlan;

class ChangePlan
{
    private $subscriptionPlan;

    private $subscription;

    public function __construct(
        Subscription $subscription,
        SubscriptionPlan $plan
    ) {
        $this->subscription = $subscription;
        $this->subscriptionPlan = $plan;
    }

    public function getSubscriptionPlan()
    {
        return $this->subscriptionPlan;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }
}
