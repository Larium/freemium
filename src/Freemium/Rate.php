<?php

declare(strict_types = 1);

namespace Freemium;

use DateTime;

trait Rate
{
    protected $rate;

    /**
     * Return the monthly rate based on date and plan.
     *
     * @param DateTime|null $data
     * @param SubscriptionPlan|null $plan
     *
     * @return int
     */
    abstract public function rate(
        DateTime $date = null,
        SubscriptionPlan $plan = null
    ) : int;

    /**
     * Gets the daily cost in cents.
     * @see RateInterface::rate method.
     *
     * @param DateTime $date
     * @param SubscriptionPlan|null $plan
     * @return int
     */
    public function getDailyRate(
        DateTime $date = null,
        SubscriptionPlan $plan = null
    ) : int {
        return (int) round($this->getYearlyRate($date, $plan) / 365, 0);
    }

    /**
     * Gets the monthly cost in cents.
     * @see RateInterface::rate method.
     *
     * @param DateTime $date
     * @param SubscriptionPlan|null $plan
     * @return int
     */
    public function getMonthlyRate(
        DateTime $date = null,
        SubscriptionPlan $plan = null
    ) : int {
        return $this->rate($date, $plan);
    }

    /**
     * Gets the yearly cost in cents.
     * @see RateInterface::rate method.
     *
     * @param DateTime $date
     * @param SubscriptionPlan|null $plan
     * @return int
     */
    public function getYearlyRate(
        DateTime $date = null,
        SubscriptionPlan $plan = null
    ) : int {
        return $this->rate($date, $plan) * 12;
    }

    /**
     * Chack if object can be paid or not.
     *
     * @return bool
     */
    public function isPaid() : bool
    {
        if ($this->rate) {
            return $this->rate > 0;
        }

        return false;
    }

    public function getRate() : int
    {
        return $this->rate;
    }
}
