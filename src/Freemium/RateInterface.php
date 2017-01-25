<?php

namespace Freemium;

use DateTime;

interface RateInterface
{
    /**
     * Compute monthly rate amount according to given options.
     *
     * @param DateTime $date The date to check available coupons for subscription.
     * @param Freemiun\SubscriptionPlanInterface $plan A plan to get the rate from.
     * @return integer
     */
    public function rate(
        DateTime $date = null,
        SubscriptionPlanInterface $plan = null
    );
}
