<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;

trait Rate
{
    /**
     * {@inheritdoc}
     */
    abstract public function getRate(): Money;

    /**
     * {@inheritdoc}
     */
    abstract public function rate(?DateTime $date = null): Money;

    /**
     * Gets the daily cost.
     *
     * @see Rateable::rate method.
     *
     * @param DateTime|null $date
     *
     * @return Money Daily rate in minor units
     */
    public function getDailyRate(?DateTime $date = null): Money
    {
        return $this->getYearlyRate($date)->divide('365', RoundingMode::HALF_UP);
    }

    /**
     * Gets the monthly cost.
     *
     * @see Rateable::rate method.
     *
     * @param DateTime|null $date
     *
     * @return Money Monthly rate in minor units
     */
    public function getMonthlyRate(?DateTime $date = null): Money
    {
        return $this->rate($date);
    }

    /**
     * Gets the yearly cost.
     *
     * @see Rateable::rate method.
     *
     * @param DateTime|null $date
     *
     * @return Money Yearly rate in minor units
     */
    public function getYearlyRate(?DateTime $date = null): Money
    {
        return $this->rate($date)->multiply(12);
    }

    /**
     * Check if an object can be paid or not.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->getRate()->greater(Money::zero($this->getRate()->getCurrency()));
    }
}
