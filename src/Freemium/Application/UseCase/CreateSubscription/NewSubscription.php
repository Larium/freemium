<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\CreateSubscription;

class NewSubscription
{
    public function __construct(
        private readonly string $customerId,
        private readonly string $subscriptionPlan,
        private readonly int $daysTrial = 0,
        private readonly int $daysGrace = 0
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

    public function getDaysTrial(): int
    {
        return $this->daysTrial;
    }

    public function getDaysGrace(): int
    {
        return $this->daysGrace;
    }
}
