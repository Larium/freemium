<?php

declare(strict_types=1);

namespace Freemium\Command\CreateSubscription;

use Freemium\RateTest;
use Freemium\Subscribable;
use Freemium\SubscriptionPlan;

class NewSubscription
{
    public function __construct(
        private readonly string $customerId,
        private readonly string $subscriptionPlan
    ) {
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getSubscriptionPlan(): string
    {
        return $this->subscriptionPlan;
    }
}
