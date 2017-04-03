<?php

namespace Freemium;

use DateTime;
use PHPUnit_Framework_TestCase as TestCase;

class CouponTest extends TestCase
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

    public function testCouponDescription()
    {
        $coupon = new Coupon(new Discount(10, Discount::PERCENTAGE));
        $description = '10% discount';
        $coupon->setDescription($description);

        $this->assertEquals($description, $coupon->getDescription());
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
