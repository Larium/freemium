<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection;

class SubscriptionPlan
{
    protected $id;

    protected $subscriptions;

    protected $coupons;

    protected $period;

    protected $frequency;

    protected $name;

    protected $rate;

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->coupons = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }
}
