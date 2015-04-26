<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

interface RateInterface
{
    /**
     * Compute rate amount according to given options.
     *
     * Available options are:
     *  date A DateTime object to check available coupons for subscription
     *  plan A Freemium\SubscriptionPlan to get the rate.
     *
     * @param array $options
     * @access public
     * @return void
     */
    public function rate(array $options = array());
}
