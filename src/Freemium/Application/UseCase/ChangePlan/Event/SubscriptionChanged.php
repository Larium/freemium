<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChangePlan\Event;

use Freemium\Domain\Subscription;
use Freemium\Application\Event\DomainEvent;

class SubscriptionChanged extends DomainEvent
{
    public const NAME = 'subscription.changed';

    private $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
