<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

class SubscriptionChange extends \Freemium\SubscriptionChange
{
    protected $id;

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
