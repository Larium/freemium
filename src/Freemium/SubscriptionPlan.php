<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

class SubscriptionPlan implements Rateable, SubscriptionPlanPeriod
{
    use Rate;

    public static $periods = [
        self::PERIOD_DAY => 'days',
        self::PERIOD_WEEK => 'weeks',
        self::PERIOD_MONTH => 'months',
        self::PERIOD_YEAR => 'years',
    ];

    /**
     * The period of plan cycle. @see SuscriptionPlanInterface
     *
     * @var int
     */
    private $period;

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
    private $frequency;

    /**
     * The name of plan
     *
     * @var string
     */
    private $name;

    private int $rate;

    public function __construct(int $period, int $frequency, int $rate, string $name)
    {
        $this->rate = $rate;
        $this->name = $name;
        $this->period = $period;
        $this->frequency = $frequency;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    /**
     * {@inheritdoc}
     */
    public function rate(?DateTime $date = null): int
    {
        $calculator = new PeriodCalculator($this->period, $this->frequency);

        return $calculator->monthlyRate($this->rate);
    }

    public function getCycleRelativeFormat(): string
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
    public function getName(): string
    {
        return $this->name;
    }
}
