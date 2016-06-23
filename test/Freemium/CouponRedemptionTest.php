<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class CouponRedemptionTest extends \PHPUnit_Framework_TestCase
{
    use FixturesHelper;

    public function testExpiry()
    {
        $coupon = $this->coupons('fifteen_percent');

        $subscription = new Subscription();

        $couponRedemption = new CouponRedemption($subscription, $coupon);

        $couponRedemption->expire();

        $this->assertEquals(new DateTime('today'), $couponRedemption->getExpiredOn());
    }

    public function testActive()
    {
        $coupon = $this->coupons('one_month_duration');

        $subscription = new Subscription();

        $couponRedemption = new CouponRedemption($subscription, $coupon);

        $this->assertTrue($couponRedemption->isActive());

        $fifteenDays = (new DateTime())->modify('+15 days');
        $this->assertTrue($couponRedemption->isActive($fifteenDays));

        $oneMonth = (new DateTime('today'))->modify('+1 month');
        $this->assertFalse($couponRedemption->isActive($oneMonth));

        $this->assertEquals($oneMonth, $couponRedemption->expiresOn());
    }

    public function testApplyCoupon()
    {
        $sub = $this->subscriptions('testApplyCoupon');

        $coupon = $this->coupons('sample');

        $original_price = $sub->rate();
        $original_remaining_amount = $sub->remainingAmount();
        $original_daily_rate = $sub->getDailyRate();

        $sub->applyCoupon($coupon);

        $this->assertFalse($sub->getCouponRedemptions()->isEmpty());
        $this->assertNotNull($sub->getCouponRedemptions()->first());
        $this->assertNotNull($sub->getCouponRedemptions()->first()->getSubscription());
        $this->assertEquals($sub->rate(), $coupon->getDiscount($original_price));
        $this->assertEquals($sub->getDailyRate(), $coupon->getDiscount($original_daily_rate));
        $this->assertEquals($sub->remainingAmount(), $coupon->getDiscount($original_daily_rate) * $sub->getRemainingDays());
    }

    public function testApplyCouponForSpecificPlan()
    {
        $sub = $this->subscriptions('testApplyCoupon');

        $coupon = $this->coupons('sample');
        $coupon->getSubscriptionPlans()->add($this->subscriptionPlans('basic'));

        $this->assertTrue($sub->applyCoupon($coupon));

        $coupon->getSubscriptionPlans()->clear();
        $coupon->getSubscriptionPlans()->add($this->subscriptionPlans('premium'));

        $this->assertFalse($sub->applyCoupon($coupon));

    }
}
