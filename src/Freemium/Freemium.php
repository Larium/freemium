<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use AktiveMerchant\Billing\Gateways\Bogus;

class Freemium
{
    public static $days_free_trial = 15;

    public static $days_grace = 3;

    public static function getGateway()
    {
        return new Bogus();
    }
}
