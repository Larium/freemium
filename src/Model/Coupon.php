<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection;

class Coupon
{
    protected $description;

    protected $discount_percentage;

    protected $discount_flat;

    protected $redemption_key;

    protected $redemption_limit;

    protected $redemption_expiration;

    protected $duration_in_months;

    protected $coupon_redemptions;

    protected $subscription_plans;

    protected $id;

    public function __construct()
    {
        $this->coupon_redemptions = new ArrayCollection();
        $this->subscription_plans = new ArrayCollection();
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
