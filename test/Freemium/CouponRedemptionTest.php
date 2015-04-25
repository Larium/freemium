<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use Helper;

class CouponRedemptionTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    public function testExpiry()
    {

        $coupon = $this->coupons('fifteen_percent');

        $subscription = new Subscription();

        $couponRedemption = new CouponRedemption();
        $couponRedemption->setCoupon($coupon);
        $couponRedemption->setSubscription($subscription);

        $couponRedemption->expire();

        $this->assertEquals(new DateTime('today'), $couponRedemption->getExpiredOn());
    }

    public function testActive()
    {
        $coupon = $this->coupons('one_month_duration');

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
