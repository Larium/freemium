<?php

namespace Freemium\Domain;

use PHPUnit\Framework\TestCase;

class PeriodCalculatorTest extends TestCase
{
    public function testCalculation()
    {
        $calc = new PeriodCalculator(
            SubscriptionPlan::PERIOD_DAY,
            20
        );

        $rate = $calc->monthlyRate(Money::ofMinor('2495', 'USD'));

        $this->assertTrue($rate->equals(Money::ofMinor('3743', 'USD')));
    }

    public function testWeekCalculation()
    {
        $calc = new PeriodCalculator(
            SubscriptionPlan::PERIOD_WEEK,
            20
        );

        $rate = $calc->monthlyRate(Money::ofMinor('2495', 'USD'));

        $this->assertTrue($rate->equals(Money::ofMinor('499', 'USD')));
    }
}
