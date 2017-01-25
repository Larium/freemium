<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Doctrine\Common\Collections\ArrayCollection;

class SubscriptionPlan implements SubscriptionPlanInterface
{
    use Rate;

    public static $periods = array(
        self::PERIOD_DAY => 'days',
        self::PERIOD_WEEK => 'weeks',
        self::PERIOD_MONTH => 'months',
        self::PERIOD_YEAR => 'years',
    );

    /**
     * Coupons for this subscription plan
     *
     * @var ArrayCollection<Freemium\Coupon>
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
        $format = self::$periods[$this->period];
        $frequency = $this->frequency;

        return "{$frequency} {$format}";
    }

    /**
     * Get the name of the subscrition plan.
     *
     * @return string.
     */
    public function getName()
    {
        return $this->name;
    }
}
