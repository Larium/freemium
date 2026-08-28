<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription\Event;

use Freemium\Domain\Subscription;
use Freemium\Application\Event\DomainEvent;

class SubscriptionPaid extends DomainEvent
{
    public const NAME = 'subscription.paid';

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
