<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class CouponRedemptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExpiry()
    {
        $coupon = new Coupon();
        $coupon->setProperties([
            'description' => 'Discount coupon',
            'discount_percentage' => 15
        ]);

        $subscription = new Subscription();

        $couponRedemption = new CouponRedemption();
        $couponRedemption->setCoupon($coupon);
        $couponRedemption->setSubscription($subscription);

        $couponRedemption->expire();

        $this->assertEquals(new DateTime('today'), $couponRedemption->getExpiredOn());
    }

    public function testActive()
    {
        $coupon = new Coupon();
        $coupon->setProperties([
            'description' => 'Discount coupon',
            'discount_percentage' => 15,
            'duration_in_months' => 1
        ]);

        $subscription = new Subscription();

        $couponRedemption = new CouponRedemption();
        $couponRedemption->setCoupon($coupon);
        $couponRedemption->setSubscription($subscription);

        $this->assertTrue($couponRedemption->isActive());

        $fifteenDays = (new DateTime())->modify('+15 days');
        $this->assertTrue($couponRedemption->isActive($fifteenDays));

        $oneMonth = (new DateTime('today'))->modify('+1 month');
        $this->assertFalse($couponRedemption->isActive($oneMonth));

        $this->assertEquals($oneMonth, $couponRedemption->expiresOn());
    }
}
