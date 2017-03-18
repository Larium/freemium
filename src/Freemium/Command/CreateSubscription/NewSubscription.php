<?php

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

    public function getSubscribable()
    {
        return $this->subscribable;
    }

    public function getSubscriptionPlan()
    {
        return $this->subscriptionPlan;
    }
}
