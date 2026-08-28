<?php

declare(strict_types=1);

namespace Freemium\Domain;

use PHPUnit\Framework\TestCase;

class RateTest extends TestCase
{
    use FixturesHelper;

    public function testDailyRate(): void
    {
        $calculator = new RateCalculator();
        $plan = $this->subscriptionPlans('basic');
        $daily = $calculator->dailyRate($plan);
        $this->assertInstanceOf(Money::class, $daily);
        $this->assertTrue($daily->getCurrency() === 'USD');
        $yearly = $calculator->yearlyRate($plan);
        $expectedDaily = $yearly->divide('365', RoundingMode::HALF_UP);
        $this->assertTrue($daily->equals($expectedDaily));
    }

    public function testMonthlyRate(): void
    {
        $calculator = new RateCalculator();
        $plan = $this->subscriptionPlans('basic');
        $monthly = $calculator->monthlyRate($plan);
        $this->assertInstanceOf(Money::class, $monthly);
        $this->assertTrue($monthly->equals($plan->getRate()));
    }

    public function testYearlyRate(): void
    {
        $calculator = new RateCalculator();
        $plan = $this->subscriptionPlans('basic');
        $yearly = $calculator->yearlyRate($plan);
        $this->assertInstanceOf(Money::class, $yearly);
        $this->assertTrue($yearly->equals($plan->getRate()->multiply(12)));
    }

    public function testBillingAmountWithoutCoupon(): void
    {
        $calculator = new RateCalculator();
        $plan = $this->subscriptionPlans('basic');
        $amount = $calculator->billingAmount($plan);
        $this->assertTrue($amount->equals($plan->getRate()));
    }

    public function testBillingAmountWithCoupon(): void
    {
        $calculator = new RateCalculator();
        $plan = $this->subscriptionPlans('basic');
        $coupon = $this->coupons('sample');
        $amount = $calculator->billingAmount($plan, $coupon);
        $this->assertTrue($amount->equals($coupon->getDiscount($plan->getRate())));
    }

    public function testIsPaidOnSubscriptionPlan(): void
    {
        $free = $this->subscriptionPlans('free');
        $basic = $this->subscriptionPlans('basic');
        $this->assertFalse($free->isPaid());
        $this->assertTrue($basic->isPaid());
    }
}
