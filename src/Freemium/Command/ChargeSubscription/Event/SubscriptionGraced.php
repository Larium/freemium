<?php

declare(strict_types=1);

namespace Freemium\Command\ChargeSubscription\Event;

use Freemium\Subscription;
use Freemium\Event\DomainEvent;

class SubscriptionGraced extends DomainEvent
{
    const NAME = 'subscription.paid';

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
