<?php

declare(strict_types=1);

namespace Freemium\Event\Subscription;

use Freemium\Subscription;
use Freemium\Event\DomainEvent;

class SubscriptionExpired extends DomainEvent
{
    const NAME = 'subscription.expired';

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
