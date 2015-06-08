<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

class Subscription extends \Freemium\Subscription
{
    private $id;

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
