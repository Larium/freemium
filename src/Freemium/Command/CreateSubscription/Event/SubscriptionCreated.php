<?php

namespace Freemium\Command\CreateSubscription\Event;

use Freemium\Subscription;
use Freemium\Event\DomainEvent;

class SubscriptionCreated extends DomainEvent
{
    public const NAME = 'subscription.created';

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
