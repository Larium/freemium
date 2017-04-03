<?php

declare(strict_types = 1);

namespace Freemium\Command\CreateSubscription;

use Freemium\SubscribableInterface;
use Freemium\SubscriptionPlanInterface;

class NewSubscription
{
    /**
     * @var SubscribableInterface
     */
    private $subscribable;

    /**
     * @var SubscriptionPlanInterface
     */
    private $subscriptionPlan;

    public function __construct(
        SubscribableInterface $subscribable,
        SubscriptionPlanInterface $subscriptionPlan
    ) {
        $this->subscribable = $subscribable;
        $this->subscriptionPlan = $subscriptionPlan;
    }

    public function getSubscribable() : SubscribableInterface
    {
        return $this->subscribable;
    }

    public function getSubscriptionPlan() : SubscriptionPlanInterface
    {
        return $this->subscriptionPlan;
    }
}
