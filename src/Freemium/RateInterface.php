<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

interface RateInterface
{
    /**
     * Compute monthly rate amount according to given options.
     *
     * Available options are:
     *  date A DateTime object to check available coupons for subscription
     *  plan A Freemium\SubscriptionPlan to get the rate.
     *
     * @param array $options
     * @return integer
     */
    public function rate(array $options = []);
}
