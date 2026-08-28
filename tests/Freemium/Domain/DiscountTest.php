<?php

namespace Freemium\Domain;

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
        $amountMoney = Money::ofMinor((string) $amount, 'USD');

        $value = $discount->apply($amountMoney);

        $this->assertTrue($value->equals(Money::ofMinor((string) $result, 'USD')));
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
        $amountMoney = Money::ofMinor('100', 'USD');

        $value = $discount->apply($amountMoney);

        $this->assertTrue($value->equals(Money::ofMinor('80', 'USD')));
    }

    public static function getRates()
    {
        return [
            [10, 100, Discount::PERCENTAGE, 90],
            [50, 100, Discount::PERCENTAGE, 50],
        ];
    }
}
