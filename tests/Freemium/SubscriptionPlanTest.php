<?php

namespace Freemium;

use PHPUnit\Framework\TestCase;
use Freemium\SubscriptionPlan as Plan;

class SubscriptionPlanTest extends TestCase
{
    use FixturesHelper;

    /**
     * @dataProvider dataProvider
     */
    public function testCycleRelativeFormat($expected, $period, $frequency)
    {
        $plan = new SubscriptionPlan($period, $frequency, 5000, 'basic');
        $r = $plan->getCycleRelativeFormat();
        $this->assertEquals($expected, $r);
    }

    public function testPlanRate(): void
    {
        $plan = $this->subscriptionPlans('basic');
        $rate = $plan->getRate();
        $r = $plan->rate();

        $this->assertEquals($rate, $r);
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
