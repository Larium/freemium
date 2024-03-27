<?php

declare(strict_types=1);

namespace Freemium;

/**
 * Calculates monthly rate of a plan for a give period and frequency.
 *
 * @author Andreas Kollaros <andreas@larium.net>
 */
class PeriodCalculator
{
    private $period;

    private $frequency;

    public function __construct(int $period, int $frequency)
    {
        $this->period = $period;
        $this->frequency = $frequency;
    }

    /**
     * Calculate monthly rate.
     *
     * @param int $rate The rate to calculate
     *
     * @return int
     */
    public function monthlyRate(int $rate): int
    {
        switch (true) {
            case $this->period == SubscriptionPlan::PERIOD_DAY:
                $months = $this->frequency / 30;
                return $this->rate($months, $rate);
            case $this->period == SubscriptionPlan::PERIOD_WEEK:
                $months = $this->frequency / 4;
                return $this->rate($months, $rate);
            case $this->period == SubscriptionPlan::PERIOD_YEAR:
                $months = $this->frequency * 12;
                return $this->rate($months, $rate);
            default:
                return $this->rate($this->frequency, $rate);
        }
    }

    private function rate(float $months, int $rate): int
    {
        return (int) round($rate / $months, 0);
    }
}
