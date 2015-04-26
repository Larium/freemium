<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class RateClass {

    use Rate;

    public function __construct($rate = 0)
    {
        $this->rate = $rate ?: 1000; # 10 dolars
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

        $this->assertEquals(32.876712328767, $rate->getDailyRate());
    }

    public function testMonthlyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(1000, $rate->getMonthlyRate());
    }

    public function testYeatlyRate()
    {
        $rate = new RateClass();
        $this->assertEquals(12000, $rate->getYearlyRate());
    }
}
