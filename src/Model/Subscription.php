<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

class Subscription extends \Freemium\Subscription
{
    protected $id;

    protected $subscription_change_class = 'Model\\SubscriptionChange';

    /**
     * Gets id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
