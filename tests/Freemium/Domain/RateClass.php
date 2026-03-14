<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;

class RateClass implements Rateable
{
    use Rate;

    private Money $rate;

    public function __construct($rate = null)
    {
        $minor = null === $rate ? '1000' : (string) $rate;
        $this->rate = Money::ofMinor($minor, 'USD');
    }

    public function getRate(): Money
    {
        return $this->rate;
    }

    public function rate(?DateTime $date = null): Money
    {
        return $this->rate;
    }
}
