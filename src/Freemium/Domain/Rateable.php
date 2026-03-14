<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;

interface Rateable
{
    /**
     * Calculate monthly rate amount according to given date.
     *
     * @param DateTime|null $date The date to check available coupons for subscription.
     *
     * @return Money Monthly rate in minor units
     */
    public function rate(?DateTime $date = null): Money;

    /**
     * Return the fixed rate of the object.
     *
     * @return Money Plan/subscription rate in minor units
     */
    public function getRate(): Money;
}
