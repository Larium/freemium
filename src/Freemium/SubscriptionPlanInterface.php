<?php

declare(strict_types = 1);

namespace Freemium;

interface SubscriptionPlanInterface
{
    const PERIOD_DAY = 1;

    const PERIOD_WEEK = 2;

    const PERIOD_MONTH = 3;

    const PERIOD_YEAR = 4;

    public function getRate() : int;

    public function rate(array $options = []) : int;
}
