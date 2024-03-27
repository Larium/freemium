<?php

declare(strict_types=1);

namespace Freemium\Command\CreateSubscription;

use Freemium\SubscriptionPlan;
use Freemium\SubscribableInterface;

class NewSubscription
{
    /**
     * @var SubscribableInterface
     */
    private $subscribable;

    /**
     * @var SubscriptionPlan
     */
    private $subscriptionPlan;

    public function __construct(
        SubscribableInterface $subscribable,
        SubscriptionPlan $subscriptionPlan
    ) {
        $this->subscribable = $subscribable;
        $this->subscriptionPlan = $subscriptionPlan;
    }

    public function getSubscribable(): SubscribableInterface
    {
        return $this->subscribable;
    }

    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }
}
