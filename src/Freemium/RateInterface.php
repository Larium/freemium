<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

interface RateInterface
{
    /**
     * Compute monthly rate amount according to given options.
     *
     * @param DateTime $date The date to check available coupons for subscription.
     * @param SubscriptionPlan $plan A plan to get the rate from.
     * @return int
     */
    public function rate(
        DateTime $date = null,
        SubscriptionPlan $plan = null
    ): int;
}
