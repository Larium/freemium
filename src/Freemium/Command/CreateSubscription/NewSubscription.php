<?php

namespace Freemium\Command\CreateSubscription;

use Freemium\SubscribableInterface;
use Freemium\SubscriptionPlanInterface;

class NewSubscription
{
    /**
     * @var SubscribableInterface
     */
    public $subscribable;

    /**
     * @var SubscriptionPlanInterface
     */
    public $subscriptionPlan;

    public function __construct(
        SubscribableInterface $subscribable,
        SubscriptionPlanInterface $subscriptionPlan
    ) {
        $this->subscribable = $subscribable;
        $this->subscriptionPlan = $subscriptionPlan;
    }
}
