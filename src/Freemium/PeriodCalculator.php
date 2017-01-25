<?php

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

    public function __construct($period, $frequency)
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
    public function monthlyRate($rate)
    {
        switch (true) {
            case $this->period == SubscriptionPlanInterface::PERIOD_DAY:
                $months = $this->frequency / 30;
                return $this->rate($months, $rate);
            case $this->period == SubscriptionPlanInterface::PERIOD_WEEK:
                $months = $this->frequency / 4;
                return $this->rate($months, $rate);
            case $this->period == SubscriptionPlanInterface::PERIOD_MONTH:
                return $this->rate($this->frequency, $rate);
            case $this->period == SubscriptionPlanInterface::PERIOD_YEAR:
                $months = $this->frequency * 12;
                return $this->rate($months, $rate);
        }
    }

    private function rate($months, $rate)
    {
        return (int) round($rate / $months, 0);
    }
}
