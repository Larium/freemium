<?php

declare(strict_types=1);

namespace Freemium\Domain;

class SubscriptionPlan implements SubscriptionPlanPeriod
{
    public const TOKEN_PREFIX = 'pln_';

    private readonly string $token;

    public static array $periods = [
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
    private int $period;

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
    private int $frequency;

    /**
     * The name of plan
     *
     * @var string
     */
    private string $name;

    private Money $rate;

    public function __construct(string $token, int $period, int $frequency, Money $rate, string $name)
    {
        $this->token = $token;
        $this->rate = $rate;
        $this->name = $name;
        $this->period = $period;
        $this->frequency = $frequency;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRate(): Money
    {
        return $this->rate;
    }

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function getFrequency(): int
    {
        return $this->frequency;
    }

    public function isPaid(): bool
    {
        return $this->rate->greater(Money::zero($this->rate->getCurrency()));
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
