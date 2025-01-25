<?php

namespace Freemium\Application\UseCase\CreateSubscription\Event;

use Freemium\Domain\Subscription;
use Freemium\Application\Event\DomainEvent;

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
