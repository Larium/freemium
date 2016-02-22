<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
}
