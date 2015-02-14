<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class CouponTest extends \PHPUnit_Framework_TestCase
{
    public function testCouponExpiration()
    {
        $coupon = new Coupon();
        $coupon->setProperties([
            'description' => 'Discount coupon',
            'discount_percentage' => 15
        ]);

        $this->assertFalse($coupon->hasExpired());

        $coupon = new Coupon();
        $coupon->setProperties([
            'description' => 'Discount coupon',
            'discount_percentage' => 15,
            'redemption_expiration' => new DateTime('1 month ago')
        ]);

        $this->assertTrue($coupon->hasExpired());
    }
}
