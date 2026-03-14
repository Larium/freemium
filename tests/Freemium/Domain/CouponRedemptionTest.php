<?php

namespace Freemium\Domain;

use DateTime;
use PHPUnit\Framework\TestCase;

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

        $sub->applyCoupon($coupon);

        $redemptions = $sub->getCouponRedemptions();
        $this->assertNotEmpty($sub->getCouponRedemptions());
        $this->assertNotNull(reset($redemptions));
        $this->assertNotNull(reset($redemptions)->getSubscription());
        // After applying coupon, subscription rate equals discount applied to original monthly rate.
        $this->assertTrue($sub->rate()->equals($coupon->getDiscount($original_price)));
        // Daily rate is derived from the (discounted) monthly rate; use same path as domain for exact equality.
        $this->assertTrue($sub->getDailyRate()->equals($sub->rate()->multiply(12)->divide('365', RoundingMode::HALF_UP)));
        // Remaining amount is daily rate × remaining days.
        $this->assertTrue($sub->remainingAmount()->equals($sub->getDailyRate()->multiply((string) $sub->getRemainingDays())));
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
