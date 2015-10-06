<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

use Freemium\SubscribableInterface;

abstract class Subscribable implements SubscribableInterface
{
    protected $id;

    protected $subscriptions;

    protected $subscription_changes;

    /**
     * Gets subscriptions.
     *
     * @return mixed
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * Sets subscriptions.
     *
     * @param mixed $subscriptions the value to set.
     * @return void
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * Gets subscription_changes.
     *
     * @return mixed
     */
    public function getSubscriptionChanges()
    {
        return $this->subscription_changes;
    }

    /**
     * Sets subscription_changes.
     *
     * @param mixed $subscription_changes the value to set.
     * @return void
     */
    public function setSubscriptionChanges($subscription_changes)
    {
        $this->subscription_changes = $subscription_changes;
    }
}
