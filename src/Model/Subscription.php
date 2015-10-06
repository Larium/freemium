<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

use Freemium\Subscription as FreemiumSubscription;
use Freemium\RateInterface;
use SplSubject;

class Subscription implements RateInterface, SplSubject
{
    use FreemiumSubscription;

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
