<?php

namespace Freemium;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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

    public function testFailConstruct()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid discount type');

        $discount = new Discount(10, 3);
    }

    public function testFlatAmount()
    {
        $discount = new Discount(20, Discount::FLAT);

        $value = $discount->apply(100);

        $this->assertEquals(80, $value);
    }

    public static function getRates()
    {
        return array(
            array(10, 100, Discount::PERCENTAGE, 90),
            array(50, 100, Discount::PERCENTAGE, 50),
        );
    }
}
