<?php

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
        return static::$expired_plan;
    }

    public static function setExpiredPlan($plan)
    {
        static::$expired_plan = $plan;
    }

    public static function setExpiredPlanKey($key)
    {
        static::$expired_plan_key = $key;
        static::$expired_plan = null;
    }

    public static function getExpiredPlanKey()
    {
        return static::$expired_plan_key;
    }
}
