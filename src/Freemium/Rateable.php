<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

interface Rateable
{
    /**
     * Calculate monthly rate amount according to given date.
     *
     * @param DateTime|null $date The date to check available coupons for subscription.
     * @return int
     */
    public function rate(?DateTime $date = null): int;

    /**
     * Return the fixed rate of the object
     */
    public function getRate(): int;
}
