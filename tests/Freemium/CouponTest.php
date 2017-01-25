<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class CouponTest extends \PHPUnit_Framework_TestCase
{
    use FixturesHelper;

    public function testCouponExpiration()
    {
        $coupon = $this->coupons('fifteen_percent');

        $this->assertFalse($coupon->hasExpired());
    }

    public function testApplySubscriptionPlan()
    {
        $coupon = $this->coupons('fifteen_percent');
        $this->getPlans($coupon);

        $free = $this->subscriptionPlans('free');
        $this->assertFalse($coupon->appliesToPlan($free));
    }

    private function getPlans(Coupon $coupon)
    {
        $coupon->addSubscriptionPlan(
            $this->subscriptionPlans('basic')
        );
        $coupon->addSubscriptionPlan(
            $this->subscriptionPlans('premium')
        );
    }
}
