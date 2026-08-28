<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\CreateSubscription;

class NewSubscription
{
    public function __construct(
        private readonly string $token,
        private readonly string $customerId,
        private readonly string $subscriptionPlan
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
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
