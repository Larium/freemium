<?php

declare(strict_types=1);

namespace Freemium\Domain;

use InvalidArgumentException;

class Discount
{
    public const FLAT = 1;

    public const PERCENTAGE = 2;

    public function __construct(private int $rate, private int $type)
    {
        if (!in_array($type, [self::PERCENTAGE, self::FLAT])) {
            throw new InvalidArgumentException('Invalid discount type');
        }

        $this->rate = $rate;
        $this->type = $type;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Applies discount to given amount and returns it.
     *
     * @param Money $amount Amount in minor units (same currency as result)
     *
     * @return Money Discounted amount in same currency
     */
    public function apply(Money $amount): Money
    {
        switch ($this->type) {
            case self::PERCENTAGE:
                return $amount->multiply(100 - $this->rate, RoundingMode::HALF_UP)->divide(100, RoundingMode::HALF_UP);
            default:
                return $amount->subtract(Money::ofMinor((string) $this->rate, $amount->getCurrency()));
        }
    }
}
