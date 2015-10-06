<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

use Freemium\SubscriptionPlan as FreemiumSubscriptionPlan;
use Freemium\SubscriptionPlanInterface;

class SubscriptionPlan implements SubscriptionPlanInterface
{
    use FreemiumSubscriptionPlan;

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
