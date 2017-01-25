<?php

namespace Freemium\Command\Subscription;

use Freemium\SubscribableInterface;
use Freemium\SubscriptionPlanInterface;

class NewSubscription
{
    /**
     * @var Freemium\SubscribableInterface
     */
    public $subscribable;

    /**
     * @var Freemium\SubscriptionPlanInterface
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
