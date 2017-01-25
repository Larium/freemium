<?php

namespace Model;

use DateTime;

class CouponRedemption
{
    protected $id;

    protected $coupon;

    protected $subscription;

    protected $redeemed_on;

    protected $expired_on;

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
        $this->redeemed_on = new DateTime('today');
    }
}
