<?php

declare(strict_types=1);

namespace Freemium;

use Freemium\Math\Calculator;

/**
 * Calculates monthly rate of a plan for a give period and frequency.
 *
 * @author Andreas Kollaros <andreas@larium.net>
 */
class PeriodCalculator
{
    private int $period;

    private int $frequency;

    private Calculator $calculator;

    public function __construct(int $period, int $frequency)
    {
        $this->period = $period;
        $this->frequency = $frequency;
        $this->calculator = new Calculator();

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

        switch ($this->period) {
            case SubscriptionPlan::PERIOD_DAY:
                $months = $this->calculator->divide(strval($this->frequency), '30', 4);
                return $this->rate($months, $rate);
            case SubscriptionPlan::PERIOD_WEEK:
                $months = $this->calculator->divide(strval($this->frequency), '4', 4);
                return $this->rate($months, $rate);
            case SubscriptionPlan::PERIOD_YEAR:
                $months = $this->calculator->multiple(strval($this->frequency), '12');
                return $this->rate($months, $rate);
            default:
                return $this->rate(strval($this->frequency), $rate);
        }
    }

    private function rate(string $months, int $rate): int
    {
        return intval($this->calculator->divide(strval($rate), $months, 0));
    }
}
