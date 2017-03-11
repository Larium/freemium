<?php

namespace Freemium;

class PeriodCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCalculation()
    {
        $calc = new PeriodCalculator(
            SubscriptionPlanInterface::PERIOD_DAY,
            20
        );

        $rate = $calc->monthlyRate(2495);

        $this->assertEquals(3743, $rate);
    }

    public function testWeekCalculation()
    {
        $calc = new PeriodCalculator(
            SubscriptionPlanInterface::PERIOD_WEEK,
            20
        );

        $rate = $calc->monthlyRate(2495);

        $this->assertEquals(499, $rate);
    }
}
