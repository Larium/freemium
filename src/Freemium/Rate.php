<?php

namespace Freemium;

use DateTime;
use Exception;

trait Rate
{
    protected $rate;

    /**
     * Gets the daily cost in cents.
     * @see Freemium\RateInterface::rate method.
     *
     * @return integer
     */
    public function getDailyRate(
        DateTime $date = null,
        SubscriptionPlanInterface $plan = null
    ) {
        return (int) round($this->getYearlyRate($date, $plan) / 365, 0);
    }

    /**
     * Gets the monthly cost in cents.
     * @see Freemium\RateInterface::rate method.
     *
     * @param array $options
     * @return integer
     */
    public function getMonthlyRate(
        DateTime $date = null,
        SubscriptionPlanInterface $plan = null
    ) {
        return $this->rate($date, $plan);
    }

    /**
     * Gets the yearly cost in cents.
     * @see Freemium\RateInterface::rate method.
     *
     * @param array $options
     * @return integer
     */
    public function getYearlyRate(
        DateTime $date = null,
        SubscriptionPlanInterface $plan = null
    ) {
        return $this->rate($date, $plan) * 12;
    }

    /**
     * Chack if object can be paid or not.
     *
     * @return boolean
     */
    public function isPaid()
    {
        if ($this->rate) {
            return $this->rate > 0;
        }

        return false;
    }

    public function getRate()
    {
        return $this->rate;
    }
}
