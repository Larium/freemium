<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Freemium\Gateways\Bogus;

class Freemium
{
    public static $days_free_trial = 0;

    public static $days_grace = 3;

    protected static $expired_plan_key = 'free';

    protected static $expired_plan;

    public static function getGateway()
    {
        return new Bogus();
    }

    public static function getExpiredPlan()
    {
        if (self::$expired_plan) {
            return self::$expired_plan;
        }
    }

    public static function setExpiredPlanKey($key)
    {
        self::$expired_plan_key = $key;
        self::$expired_plan = null;

    }

    public static function getExpiredPlanKey()
    {
        return self::$expired_plan_key;
    }
}
