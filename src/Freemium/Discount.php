<?php

declare(strict_types=1);

namespace Freemium;

use InvalidArgumentException;

class Discount
{
    public const FLAT = 1;

    public const PERCENTAGE = 2;

    private $type;

    private $rate;

    public function __construct(int $rate, int $type)
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
     * @param int $amount
     * @return int
     */
    public function apply(int $amount): int
    {
        switch ($this->type) {
            case self::PERCENTAGE:
                return (int) floor($amount - ($amount * ($this->rate / 100)));
            default:
                return $amount - $this->rate;
        }
    }
}
