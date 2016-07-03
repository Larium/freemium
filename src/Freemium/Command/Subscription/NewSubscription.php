<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Command\Subscription;

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
}
