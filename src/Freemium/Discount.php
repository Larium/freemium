<?php

namespace Freemium;

use InvalidArgumentException;

class Discount
{
    const FLAT = 1;

    const PERCENTAGE = 2;

    private $type;

    private $rate;

    public function __construct($rate, $type)
    {
        if (!in_array($type, array(self::PERCENTAGE, self::FLAT))) {
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
    public function apply($amount)
    {
        switch ($this->type) {
            case self::PERCENTAGE:
                return (int) floor($amount - ($amount * ($this->rate / 100)));
            default:
                return $amount - $this->rate;
        }
    }
}
