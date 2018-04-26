<?php

namespace Freemium;

use DateTime;
use PHPUnit\Framework\TestCase;

class RateTest extends TestCase
{
    public function testDailyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(33, $rate->getDailyRate());
        $this->assertTrue(is_int($rate->getDailyRate()));
    }

    public function testMonthlyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(1000, $rate->getMonthlyRate());
        $this->assertTrue(is_int($rate->getMonthlyRate()));
    }

    public function testYearlyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(12000, $rate->getYearlyRate());
        $this->assertTrue(is_int($rate->getYearlyRate()));
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
