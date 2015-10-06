<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

use Freemium\SubscriptionChangeInterface;
use Freemium\SubscriptionChange as FreemiumSubscriptionChange;

class SubscriptionChange implements SubscriptionChangeInterface
{
    use FreemiumSubscriptionChange;

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
