<?php

declare(strict_types=1);

namespace Freemium\Domain;

use Freemium\Domain\Exception\CurrencyMismatchException;
use Freemium\Domain\Exception\DivisionByZeroException;
use Freemium\Domain\Exception\InvalidAmountException;
use Freemium\Domain\Exception\RoundingRequiredException;
use Freemium\Domain\Exception\UnsupportedCurrencyException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testZero(): void
    {
        $zero = Money::zero('EUR');
        $this->assertSame('0', $zero->getMinorAmount());
        $this->assertSame('0.00', $zero->getAmount());
        $this->assertSame('EUR', $zero->getCurrency());
    }

    public function testOfBaseEur(): void
    {
        $money = Money::ofBase('1.50', 'EUR');
        $this->assertSame('150', $money->getMinorAmount());
        $this->assertSame('1.50', $money->getAmount());
        $this->assertSame('EUR', $money->getCurrency());
    }

    public function testOfBaseJpy(): void
    {
        $money = Money::ofBase('100', 'JPY');
        $this->assertSame('100', $money->getMinorAmount());
        $this->assertSame('100', $money->getAmount());
        $this->assertSame('JPY', $money->getCurrency());
    }

    public function testOfMinorEur(): void
    {
        $money = Money::ofMinor('150', 'EUR');
        $this->assertSame('EUR', $money->getCurrency());
        $this->assertSame('150', $money->getMinorAmount());
        $this->assertSame('1.50', $money->getAmount());
    }

    public function testOfMinorJpy(): void
    {
        $money = Money::ofMinor('1000', 'JPY');
        $this->assertSame('1000', $money->getMinorAmount());
        $this->assertSame('1000', $money->getAmount());
    }

    public function testAddSameCurrency(): void
    {
        $a = Money::ofBase('10.00', 'EUR');
        $b = Money::ofBase('5.50', 'EUR');
        $sum = $a->add($b);
        $this->assertSame('1550', $sum->getMinorAmount());
        $this->assertSame('15.50', $sum->getAmount());
        $this->assertSame('EUR', $sum->getCurrency());
    }

    public function testSubtractSameCurrency(): void
    {
        $a = Money::ofBase('10.00', 'EUR');
        $b = Money::ofBase('3.25', 'EUR');
        $diff = $a->subtract($b);
        $this->assertSame('675', $diff->getMinorAmount());
        $this->assertSame('6.75', $diff->getAmount());
    }

    public function testMultiply(): void
    {
        $money = Money::ofMinor('100', 'EUR');
        $double = $money->multiply(2);
        $this->assertSame('200', $double->getMinorAmount());
        $half = $money->multiply('0.5');
        $this->assertSame('50', $half->getMinorAmount());
    }

    public function testDivide(): void
    {
        $money = Money::ofMinor('100', 'EUR');
        $half = $money->divide(2);
        $this->assertSame('50', $half->getMinorAmount());
        $third = $money->divide('2');
        $this->assertSame('50', $third->getMinorAmount());
    }

    public function testNegate(): void
    {
        $money = Money::ofBase('1.50', 'EUR');
        $neg = $money->negate();
        $this->assertSame('-150', $neg->getMinorAmount());
        $this->assertSame('-1.50', $neg->getAmount());
    }

    public function testEqualsSameAmountAndCurrency(): void
    {
        $a = Money::ofBase('1.50', 'EUR');
        $b = Money::ofBase('1.50', 'EUR');
        $this->assertTrue($a->equals($b));
    }

    public function testEqualsDifferentAmount(): void
    {
        $a = Money::ofBase('1.50', 'EUR');
        $b = Money::ofBase('1.51', 'EUR');
        $this->assertFalse($a->equals($b));
    }

    public function testEqualsDifferentCurrencyThrows(): void
    {
        $a = Money::ofBase('1.50', 'EUR');
        $b = Money::ofBase('1.50', 'USD');
        $this->expectException(CurrencyMismatchException::class);
        $a->equals($b);
    }

    public function testGreater(): void
    {
        $a = Money::ofBase('2.00', 'EUR');
        $b = Money::ofBase('1.50', 'EUR');
        $this->assertTrue($a->greater($b));
        $this->assertFalse($b->greater($a));
    }

    public function testLess(): void
    {
        $a = Money::ofBase('1.00', 'EUR');
        $b = Money::ofBase('2.50', 'EUR');
        $this->assertTrue($a->less($b));
        $this->assertFalse($b->less($a));
    }

    public function testWithAmount(): void
    {
        $money = Money::ofBase('1.50', 'EUR');
        $updated = $money->withAmount('300');
        $this->assertSame('300', $updated->getMinorAmount());
        $this->assertSame('EUR', $updated->getCurrency());
    }

    public function testJsonSerialize(): void
    {
        $money = Money::ofBase('1.50', 'EUR');
        $data = $money->jsonSerialize();
        $this->assertSame('1.50', $data['amount']);
        $this->assertSame('150', $data['minorAmount']);
        $this->assertSame('EUR', $data['currency']);
    }

    public function testGreaterOrEqualTo(): void
    {
        $a = Money::ofBase('2.00', 'EUR');
        $b = Money::ofBase('1.50', 'EUR');
        $c = Money::ofBase('2.00', 'EUR');
        $this->assertTrue($a->greaterOrEqualTo($b));
        $this->assertTrue($a->greaterOrEqualTo($c));
        $this->assertFalse($b->greaterOrEqualTo($a));
    }

    public function testLessOrEqualTo(): void
    {
        $a = Money::ofBase('1.00', 'EUR');
        $b = Money::ofBase('2.50', 'EUR');
        $c = Money::ofBase('1.00', 'EUR');
        $this->assertTrue($a->lessOrEqualTo($b));
        $this->assertTrue($a->lessOrEqualTo($c));
        $this->assertFalse($b->lessOrEqualTo($a));
    }

    public function testAddDifferentCurrenciesThrows(): void
    {
        $a = Money::ofBase('1.50', 'EUR');
        $b = Money::ofBase('1.50', 'USD');
        $this->expectException(CurrencyMismatchException::class);
        $a->add($b);
    }

    public function testSubtractDifferentCurrenciesThrows(): void
    {
        $a = Money::ofBase('1.50', 'EUR');
        $b = Money::ofBase('1.50', 'USD');
        $this->expectException(CurrencyMismatchException::class);
        $a->subtract($b);
    }

    public function testDivideByZeroThrows(): void
    {
        $money = Money::ofMinor('100', 'EUR');
        $this->expectException(DivisionByZeroException::class);
        $money->divide(0);
    }

    public function testInvalidAmountThrows(): void
    {
        $this->expectException(InvalidAmountException::class);
        Money::ofBase('abc', 'EUR');
    }

    public function testUnsupportedCurrencyThrows(): void
    {
        $this->expectException(UnsupportedCurrencyException::class);
        Money::ofBase('1.50', 'XXX');
    }

    public function testEmptyCurrencyThrows(): void
    {
        $this->expectException(UnsupportedCurrencyException::class);
        Money::zero('');
    }

    public function testOfBaseExcessDecimalsThrowsWithoutRounding(): void
    {
        $this->expectException(RoundingRequiredException::class);
        Money::ofBase('1.234', 'EUR');
    }

    public function testOfBaseExcessDecimalsWithRounding(): void
    {
        $money = Money::ofBase('1.234', 'EUR', RoundingMode::HALF_UP);
        $this->assertSame('123', $money->getMinorAmount());
        $this->assertSame('1.23', $money->getAmount());
    }

    public function testDivideFractionalResultThrowsWithoutRounding(): void
    {
        $money = Money::ofMinor('100', 'EUR');
        $this->expectException(RoundingRequiredException::class);
        $money->divide(3);
    }

    public function testDivideFractionalResultWithRounding(): void
    {
        $money = Money::ofMinor('100', 'EUR');
        $result = $money->divide(3, RoundingMode::HALF_UP);
        $this->assertSame('33', $result->getMinorAmount());
    }
}
