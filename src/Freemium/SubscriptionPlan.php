<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Doctrine\Common\Collections\ArrayCollection;

class SubscriptionPlan implements SubscriptionPlanInterface
{
    use Rate;

    protected $subscriptions;

    /**
     * Coupons for this subscription plan
     *
     * @var ArrayCollection<Coupon>
     */
    protected $coupons;

    /**
     * The period of plan cycle. @see SuscriptionPlanInterface
     *
     * @var int
     */
    protected $period;

    /**
     * The billing frequency of plan period.
     *
     * The value of frequency can not exceed the logic value of a year.
     * if choosen period is days then max value should be 365.
     * if choosen period is months then max value should be 12.
     * if choosen period is weeks then max value should be 52.
     *
     * @var int
     */
    protected $frequency;

    /**
     * The name of plan
     *
     * @var string
     */
    protected $name;

    public function __construct($period, $frequency, $rate, $name)
    {
        $this->rate = $rate;
        $this->name = $name;
        $this->period = $period;
        $this->frequency = $frequency;
        $this->coupons = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function rate(array $options = [])
    {
        $plan = isset($options['plan']) ? $options['plan'] : $this;

        $calculator = new PeriodCalculator($this->period, $this->frequency);

        return $calculator->monthlyRate($plan->rate);
    }

    public function getCycleRelativeFormat()
    {
        $format = static::PERIODS[$this->period];
        $frequency = $this->frequency;

        return "{$frequency} {$format}";
    }

    /**
     * Get name.
     *
     * @return name.
     */
    public function getName()
    {
        return $this->name;
    }
}
