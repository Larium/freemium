<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
     * Applies discount to given rate and returns it.
     *
     * If discount type is percentage, will divide given
     * rate with 1 + (percentage rate / 100)
     * So in 5% discount will do rate / 1.05
     *
     * @param int $rate
     * @return int
     */
    public function calculate($rate)
    {
        switch ($this->type) {
            case self::PERCENTAGE:
                return (int) round($rate / (1 + ($this->rate / 100)), 0);
            case self::FLAT:
                return $rate - $this->rate;
        }
    }
}
