<?php

namespace Freemium\Event\Subscription;

use Freemium\Subscription;
use Freemium\Event\DomainEvent;

class SubscriptionCreated extends DomainEvent
{
    const NAME = 'subscription.created';

    private $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }
}
