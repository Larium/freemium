<?php

declare(strict_types=1);

namespace Freemium\Domain;

use Freemium\Domain\Exception\CurrencyMismatchException;
use Freemium\Domain\Exception\DivisionByZeroException;
use Freemium\Domain\Exception\InvalidAmountException;
use Freemium\Domain\Exception\RoundingRequiredException;
use Freemium\Domain\Exception\UnsupportedCurrencyException;
use JsonSerializable;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmul;
use function bcsub;

final class Money implements JsonSerializable
{
    public static function zero(string $currency): self
    {
        self::validateCurrency($currency);

        return new self('0', $currency);
    }

    public static function ofMinor(string $amount, string $currency): self
    {
        self::validateAmount($amount);
        self::validateCurrency($currency);

        return new self($amount, $currency);
    }

    public static function ofBase(string $baseAmount, string $currency, ?RoundingMode $rounding = null): self
    {
        self::validateAmount($baseAmount);
        self::validateCurrency($currency);
        $minor = self::toMinorAmount($baseAmount, $currency, $rounding);

        return new self($minor, $currency);
    }

    public function __construct(
        private readonly string $amount,
        private readonly string $currency
    ) {
        self::validateAmount($amount);
        self::validateCurrency($currency);
    }

    public function withAmount(string $amount): self
    {
        self::validateAmount($amount);

        return new self($amount, $this->currency);
    }

    public function equals(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot compare different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return bccomp($this->amount, $other->amount) === 0;
    }

    public function greater(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot compare different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return bccomp($this->amount, $other->amount) === 1;
    }

    public function less(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot compare different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return bccomp($this->amount, $other->amount) === -1;
    }

    public function greaterOrEqualTo(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot compare different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return bccomp($this->amount, $other->amount) >= 0;
    }

    public function lessOrEqualTo(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot compare different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return bccomp($this->amount, $other->amount) <= 0;
    }

    public function negate(): self
    {
        return new self(bcmul($this->amount, '-1', 0), $this->currency);
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot add different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return new self(bcadd($this->amount, $other->amount, 0), $this->currency);
    }

    public function subtract(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot subtract different currencies: ' . $this->currency . ' and ' . $other->currency);
        }

        return new self(bcsub($this->amount, $other->amount, 0), $this->currency);
    }

    public function multiply(int|string $number, ?RoundingMode $rounding = null): self
    {
        $result = bcmul($this->amount, (string) $number, 2);
        $minor = self::ensureIntegerMinor($result, $rounding);

        return new self($minor, $this->currency);
    }

    public function divide(int|string $number, ?RoundingMode $rounding = null): self
    {
        $divisor = (string) $number;
        if (bccomp($divisor, '0') === 0) {
            throw new DivisionByZeroException('Division by zero.');
        }

        $result = bcdiv($this->amount, $divisor, 2);
        $minor = self::ensureIntegerMinor($result, $rounding);

        return new self($minor, $this->currency);
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getMinorAmount(): string
    {
        return $this->amount;
    }

    public function getAmount(): string
    {
        return self::toBaseAmount($this->amount, $this->currency);
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmount(),
            'minorAmount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    private static function validateAmount(string $amount): void
    {
        if (!is_numeric($amount)) {
            throw new InvalidAmountException('Invalid amount: ' . $amount);
        }
    }

    private static function validateCurrency(string $currency): void
    {
        if ($currency === '') {
            throw new UnsupportedCurrencyException('Currency cannot be empty.');
        }
        if (!in_array($currency, Currency::supportedCurrencies(), true)) {
            throw new UnsupportedCurrencyException('Unsupported currency: ' . $currency);
        }
    }

    private static function toBaseAmount(string $minorAmount, string $currency): string
    {
        $fraction = Currency::getFraction($currency);
        $divisor = bcpow('10', (string) $fraction);

        return bcdiv($minorAmount, $divisor, $fraction);
    }

    private static function toMinorAmount(string $baseAmount, string $currency, ?RoundingMode $rounding = null): string
    {
        $fraction = Currency::getFraction($currency);
        $multiplier = bcpow('10', (string) $fraction);
        $scaled = bcmul($baseAmount, $multiplier, $fraction + 2);

        $decimalPlaces = self::decimalPlaces($baseAmount);
        if ($decimalPlaces > $fraction && $rounding === null) {
            throw new RoundingRequiredException('Base amount has more decimals than currency allows; pass a RoundingMode.');
        }

        $mode = $rounding ?? RoundingMode::HALF_UP;

        return $mode->roundToMinor($scaled);
    }

    private static function decimalPlaces(string $value): int
    {
        $pos = strpos($value, '.');
        if ($pos === false) {
            return 0;
        }

        return strlen($value) - $pos - 1;
    }

    private static function ensureIntegerMinor(string $result, ?RoundingMode $rounding): string
    {
        $float = (float) $result;
        $int = (int) $float;
        if ((float) $int !== $float && $rounding === null) {
            throw new RoundingRequiredException('Result has fractional minor units; pass a RoundingMode.');
        }
        $mode = $rounding ?? RoundingMode::HALF_UP;

        return $mode->roundToMinor($result);
    }
}
