<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

interface SubscribableInterface
{
    public function getSubscriptions();

    public function setSubscriptions($subscriptions);

    public function getSubscriptionChanges();

    public function setSubscriptionChanges($subscription_changes);
}
