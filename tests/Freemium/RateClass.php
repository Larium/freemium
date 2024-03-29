<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

class RateClass implements Rateable
{
    use Rate;

    private int $rate;

    public function __construct($rate = null)
    {
        $this->rate = null === $rate ? 1000 : $rate; # 10 dollars
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function rate(?DateTime $date = null): int
    {
        return $this->rate;
    }
}
