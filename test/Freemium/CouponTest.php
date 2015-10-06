<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class CouponTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    public function testCouponExpiration()
    {
        $coupon = $this->coupons('fifteen_percent');
        $this->assertFalse($coupon->hasExpired());

        /*
        $coupon->setData([
            'redemption_expiration' => new DateTime('1 month ago')
        ]);

        $this->assertTrue($coupon->hasExpired());*/
    }
}
