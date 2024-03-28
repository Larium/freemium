<?php

declare(strict_types=1);

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscribable;
use Freemium\SubscriptionPlan;

class NewSubscription
{
    /**
     * @var Subscribable
     */
    private $subscribable;

    /**
     * @var SubscriptionPlan
     */
    private $subscriptionPlan;

    public function __construct(
        Subscribable $subscribable,
        SubscriptionPlan $subscriptionPlan
    ) {
        $this->subscribable = $subscribable;
        $this->subscriptionPlan = $subscriptionPlan;
    }

    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }

    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }
}
