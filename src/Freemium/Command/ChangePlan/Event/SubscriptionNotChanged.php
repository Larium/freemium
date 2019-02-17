<?php

declare(strict_types = 1);

namespace Freemium\Command\ChangePlan\Event;

use Throwable;
use Freemium\Event\DomainEvent;
use Freemium\Subscription;
use Freemium\SubscriptionPlan;

class SubscriptionNotChanged extends DomainEvent
{
    const NAME = 'subscription.not.changed';

    private $subscription;

    private $plan;

    private $exception;

    public function __construct(
        Subscription $subscription,
        SubscriptionPlan $plan,
        Throwable $exception
    ) {
        $this->subscription = $subscription;
        $this->plan = $plan;
        $this->exception = $exception;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->plan;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }
}
