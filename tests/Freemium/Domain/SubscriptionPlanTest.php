<?php

namespace Freemium\Domain;

use PHPUnit\Framework\TestCase;
use Freemium\Domain\SubscriptionPlan as Plan;

class SubscriptionPlanTest extends TestCase
{
    use FixturesHelper;

    /**
     * @dataProvider dataProvider
     */
    public function testCycleRelativeFormat($expected, $period, $frequency)
    {
        $plan = new SubscriptionPlan($this->generatePlanToken(), $period, $frequency, Money::ofMinor('5000', 'USD'), 'basic');
        $r = $plan->getCycleRelativeFormat();
        $this->assertEquals($expected, $r);
    }

    public function testPlanRateAndMonthlyRate(): void
    {
        $plan = $this->subscriptionPlans('basic');
        $rate = $plan->getRate();
        $calculator = new RateCalculator();
        $monthlyRate = $calculator->monthlyRate($plan);
        $this->assertTrue($rate->equals($monthlyRate));
    }

    public function testGetPeriodAndFrequency(): void
    {
        $plan = $this->subscriptionPlans('basic');
        $this->assertSame(SubscriptionPlan::PERIOD_MONTH, $plan->getPeriod());
        $this->assertSame(1, $plan->getFrequency());
    }

    public static function dataProvider()
    {
        return [
            ['1 years', Plan::PERIOD_YEAR, 1],
            ['2 years', Plan::PERIOD_YEAR, 2],
            ['6 months', Plan::PERIOD_MONTH, 6],
            ['12 months', Plan::PERIOD_MONTH, 12],
            ['3 months', Plan::PERIOD_MONTH, 3],
            ['1 months', Plan::PERIOD_MONTH, 1],
            ['2 months', Plan::PERIOD_MONTH, 2],
            ['2 weeks', Plan::PERIOD_WEEK, 2],
            ['4 weeks', Plan::PERIOD_WEEK, 4],
            ['1 weeks', Plan::PERIOD_WEEK, 1],
            ['2 weeks', Plan::PERIOD_WEEK, 2],
            ['1 days', Plan::PERIOD_DAY, 1],
            ['2 days', Plan::PERIOD_DAY, 2],
        ];
    }
}
