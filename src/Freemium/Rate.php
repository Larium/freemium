<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

trait Rate
{
    /**
     * {@inheritdoc}
     */
    abstract public function getRate(): int;

    /**
     * {@inheritdoc}
     */
    abstract public function rate(?DateTime $date = null): int;

    /**
     * Gets the daily cost in cents.
     * @see Rateable::rate method.
     *
     * @param DateTime|null $date
     * @return int
     */
    public function getDailyRate(
        ?DateTime $date = null
    ): int {
        return (int) round($this->getYearlyRate($date) / 365, 0);
    }

    /**
     * Gets the monthly cost in cents.
     * @see Rateable::rate method.
     *
     * @param DateTime|null $date
     * @return int
     */
    public function getMonthlyRate(
        ?DateTime $date = null
    ): int {
        return $this->rate($date);
    }

    /**
     * Gets the yearly cost in cents.
     * @see Rateable::rate method.
     *
     * @param DateTime|null $date
     * @return int
     */
    public function getYearlyRate(
        DateTime $date = null
    ): int {
        return $this->rate($date) * 12;
    }

    /**
     * Check if an object can be paid or not.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->getRate() > 0 ?: false;
    }
}
