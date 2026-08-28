<?php

declare(strict_types=1);

namespace Freemium\Domain;

use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function testSupportedCurrenciesContainsEurAndUsd(): void
    {
        $supported = Currency::supportedCurrencies();
        $this->assertContains('EUR', $supported);
        $this->assertContains('USD', $supported);
        $this->assertContains('JPY', $supported);
        $this->assertIsArray($supported);
    }

    public function testGetFractionEur(): void
    {
        $this->assertSame(2, Currency::getFraction('EUR'));
    }

    public function testGetFractionUsd(): void
    {
        $this->assertSame(2, Currency::getFraction('USD'));
    }

    public function testGetFractionJpy(): void
    {
        $this->assertSame(0, Currency::getFraction('JPY'));
    }

    public function testGetFractionKrw(): void
    {
        $this->assertSame(0, Currency::getFraction('KRW'));
    }

    public function testGetFractionBhdThreeDecimals(): void
    {
        $this->assertSame(3, Currency::getFraction('BHD'));
    }

    public function testGetFractionUnknownCurrencyDefaultsToTwo(): void
    {
        $this->assertSame(2, Currency::getFraction('XXX'));
    }
}
