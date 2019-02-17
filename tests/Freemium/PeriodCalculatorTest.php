<?php

namespace Freemium;

use PHPUnit\Framework\TestCase;

class PeriodCalculatorTest extends TestCase
{
    public function testCalculation()
    {
        $calc = new PeriodCalculator(
            SubscriptionPlan::PERIOD_DAY,
            20
        );

        $rate = $calc->monthlyRate(2495);

        $this->assertEquals(3743, $rate);
    }

    public function testWeekCalculation()
    {
        $calc = new PeriodCalculator(
            SubscriptionPlan::PERIOD_WEEK,
            20
        );

        $rate = $calc->monthlyRate(2495);

        $this->assertEquals(499, $rate);
    }
}
