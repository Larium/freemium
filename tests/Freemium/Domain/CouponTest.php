<?php

namespace Freemium\Domain;

use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    use FixturesHelper;

    /**
     * Ensures getDiscount() accepts Money and returns Money (catches wrong signature/return type).
     */
    public function testGetDiscountAcceptsMoneyAndReturnsMoney(): void
    {
        $coupon = $this->coupons('sample'); // 5% percentage discount
        $rate = Money::ofMinor('1000', 'USD');

        $result = $coupon->getDiscount($rate);

        $this->assertInstanceOf(Money::class, $result, 'getDiscount() must return Money');
        $this->assertSame('USD', $result->getCurrency());
        $this->assertTrue($result->equals(Money::ofMinor('950', 'USD')), '5% off 1000 should be 950');
    }

    /**
     * Ensures getDiscount() delegates to Discount::apply() (same result for same rate).
     */
    public function testGetDiscountDelegatesToDiscountApply(): void
    {
        $discount = new Discount(10, Discount::PERCENTAGE);
        $coupon = new Coupon($discount);
        $rate = Money::ofMinor('500', 'EUR');

        $couponResult = $coupon->getDiscount($rate);
        $discountResult = $discount->apply($rate);

        $this->assertInstanceOf(Money::class, $couponResult);
        $this->assertTrue($couponResult->equals($discountResult), 'Coupon::getDiscount() must equal Discount::apply() for same rate');
    }

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
