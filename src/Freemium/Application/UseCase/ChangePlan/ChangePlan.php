<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChangePlan;

use Freemium\Domain\Subscription;
use Freemium\Domain\SubscriptionPlan;

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

    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
