<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class RateClass implements RateInterface {

    use Rate;

    public function __construct($rate = null)
    {
        $this->rate = null === $rate ? 1000 : $rate; # 10 dollars
    }

    public function rate(array $options = array())
    {
        return $this->rate;
    }
}

class RateTest extends \PHPUnit_Framework_TestCase
{
    public function testDailyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(32, $rate->getDailyRate());
        $this->assertTrue(is_int($rate->getDailyRate()));
    }

    public function testMonthlyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(1000, $rate->getMonthlyRate());
        $this->assertTrue(is_int($rate->getMonthlyRate()));
    }

    public function testYeatlyRate()
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
