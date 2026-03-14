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

        $couponRedemption = new CouponRedemption($this->generateRedemptionToken(), $subscription, $coupon);

        $couponRedemption->expire();

        $this->assertEquals(new DateTime('today'), $couponRedemption->getExpiredOn());
    }

    public function testActive()
    {
        $coupon = $this->coupons('one_month_duration');

        $subscription = $this->createSubscription();

        $couponRedemption = new CouponRedemption($this->generateRedemptionToken(), $subscription, $coupon);

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

        $original_price = $sub->getSubscriptionPlan()->getRate();

        $sub->applyCoupon($coupon, $this->generateRedemptionToken());

        $redemptions = $sub->getCouponRedemptions();
        $this->assertNotEmpty($sub->getCouponRedemptions());
        $this->assertNotNull(reset($redemptions));
        $this->assertNotNull(reset($redemptions)->getSubscription());
        // After applying coupon, billing amount equals discount applied to plan cycle rate.
        $this->assertTrue($sub->billingAmount()->equals($coupon->getDiscount($original_price)));
        // Remaining amount is daily rate × remaining days (Subscription uses RateCalculator internally).
        $this->assertTrue($sub->remainingAmount()->equals(
            (new RateCalculator())->dailyRate($sub->getSubscriptionPlan())->multiply((string) $sub->getRemainingDays())
        ));
    }

    public function testApplyCouponForSpecificPlan()
    {
        $sub = $this->subscriptions('testApplyCoupon');

        $coupon = $this->coupons('sample');
        $coupon->addSubscriptionPlan($this->subscriptionPlans('basic'));

        $this->assertTrue($sub->applyCoupon($coupon, $this->generateRedemptionToken()));

        $coupon->clearSubscriptionPlans();
        $coupon->addSubscriptionPlan($this->subscriptionPlans('premium'));

        $this->assertFalse($sub->applyCoupon($coupon, $this->generateRedemptionToken()));
    }

    private function createSubscription()
    {
        return new Subscription(
            $this->generateSubscriptionToken(),
            $this->users('bob'),
            $this->subscriptionPlans('free')
        );
    }
}
