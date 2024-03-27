<?php

declare(strict_types=1);

namespace Freemium;

interface SubscriptionPlanInterface
{
    public const PERIOD_DAY = 1;

    public const PERIOD_WEEK = 2;

    public const PERIOD_MONTH = 3;

    public const PERIOD_YEAR = 4;
}
