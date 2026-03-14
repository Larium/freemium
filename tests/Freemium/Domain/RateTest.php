<?php

namespace Freemium\Domain;

use PHPUnit\Framework\TestCase;

class RateTest extends TestCase
{
    public function testDailyRate()
    {
        $rate = new RateClass();
        $this->assertTrue($rate->getDailyRate()->equals(Money::ofMinor('33', 'USD')));
        $this->assertInstanceOf(Money::class, $rate->getDailyRate());
    }

    public function testMonthlyRate()
    {
        $rate = new RateClass();
        $this->assertTrue($rate->getMonthlyRate()->equals(Money::ofMinor('1000', 'USD')));
        $this->assertInstanceOf(Money::class, $rate->getMonthlyRate());
    }

    public function testYearlyRate()
    {
        $rate = new RateClass();
        $this->assertTrue($rate->getYearlyRate()->equals(Money::ofMinor('12000', 'USD')));
        $this->assertInstanceOf(Money::class, $rate->getYearlyRate());
    }

    public function testIsPaid()
    {
        $rate = new RateClass(0);
        $this->assertFalse($rate->isPaid());
        $this->assertTrue(is_bool($rate->isPaid()));

        $rate = new RateClass();
        $this->assertTrue($rate->isPaid());
        $this->assertTrue(is_bool($rate->isPaid()));
    }
}
