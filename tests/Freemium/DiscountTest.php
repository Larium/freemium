<?php

namespace Freemium;

use PHPUnit_Framework_TestCase as TestCase;

class DiscountTest extends TestCase
{
    /**
     * @dataProvider getRates
     */
    public function testSuccessConstruct($rate, $amount, $type, $result)
    {
        $discount = new Discount($rate, $type);

        $value = $discount->apply($amount);

        $this->assertEquals($result, $value);
        $this->assertEquals($rate, $discount->getRate());
        $this->assertEquals($type, $discount->getType());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid discount type
     */
    public function testFailConstruct()
    {
        $discount = new Discount(10, 3);
    }

    public function testFlatAmount()
    {
        $discount = new Discount(20, Discount::FLAT);

        $value = $discount->apply(100);

        $this->assertEquals(80, $value);
    }

    public function getRates()
    {
        return array(
            array(10, 100, Discount::PERCENTAGE, 90),
            array(50, 100, Discount::PERCENTAGE, 50),
        );
    }
}
