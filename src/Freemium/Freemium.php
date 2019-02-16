<?php

namespace Freemium;

use Freemium\Gateways\Bogus;
use Freemium\Gateways\GatewayInterface;
use Freemium\SubscriptionPlanInterface;

class Freemium
{
    public static $days_free_trial = 0;

    public static $days_grace = 3;

    protected static $expired_plan_key = 'free';

    protected static $expired_plan;

    public static function getGateway(): GatewayInterface
    {
        return new Bogus();
    }

    public static function getExpiredPlan(): SubscriptionPlanInterface
    {
        return static::$expired_plan;
    }

    public static function setExpiredPlan(SubscriptionPlanInterface $plan)
    {
        static::$expired_plan = $plan;
    }

    public static function setExpiredPlanKey(string $key)
    {
        static::$expired_plan_key = $key;
        static::$expired_plan = null;
    }
}
