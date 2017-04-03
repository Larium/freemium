<?php

namespace Freemium;

use DateTime;
use PHPUnit_Framework_TestCase as TestCase;

class CouponRedemptionTest extends TestCase
{
    use FixturesHelper;

    public function testExpiry()
    {
        $coupon = $this->coupons('fifteen_percent');

        $subscription = $this->createSubscription();

        $couponRedemption = new CouponRedemption($subscription, $coupon);

        $couponRedemption->expire();

        $this->assertEquals(new DateTime('today'), $couponRedemption->getExpiredOn());
    }

    public function testActive()
    {
        $coupon = $this->coupons('one_month_duration');

        $subscription = $this->createSubscription();

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

        $redemptions = $sub->getCouponRedemptions();
        $this->assertNotEmpty($sub->getCouponRedemptions());
        $this->assertNotNull(reset($redemptions));
        $this->assertNotNull(reset($redemptions)->getSubscription());
        $this->assertEquals($sub->rate(), $coupon->getDiscount($original_price));
        $this->assertEquals($sub->getDailyRate(), $coupon->getDiscount($original_daily_rate));
        $this->assertEquals($sub->remainingAmount(), $coupon->getDiscount($original_daily_rate) * $sub->getRemainingDays());
    }

    public function testApplyCouponForSpecificPlan()
    {
        $sub = $this->subscriptions('testApplyCoupon');

        $coupon = $this->coupons('sample');
        $coupon->addSubscriptionPlan($this->subscriptionPlans('basic'));

        $this->assertTrue($sub->applyCoupon($coupon));

        $coupon->clearSubscriptionPlans();
        $coupon->addSubscriptionPlan($this->subscriptionPlans('premium'));

        $this->assertFalse($sub->applyCoupon($coupon));
    }

    private function createSubscription()
    {
        return new Subscription(
            $this->users('bob'),
            $this->subscriptionPlans('free')
        );
    }
}
