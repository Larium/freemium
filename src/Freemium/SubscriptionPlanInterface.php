<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

interface SubscriptionPlanInterface
{
    const PERIOD_DAY = 1;

    const PERIOD_WEEK = 2;

    const PERIOD_MONTH = 3;

    const PERIOD_YEAR = 4;

    const PERIODS = [
        self::PERIOD_DAY => 'days',
        self::PERIOD_WEEK => 'weeks',
        self::PERIOD_MONTH => 'months',
        self::PERIOD_YEAR => 'years',
    ];
}
