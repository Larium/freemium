<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

use Freemium\CouponRedemption as FreemiumCouponRedemption;

class CouponRedemption
{
    use FreemiumCouponRedemption;

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
