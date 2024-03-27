<?php

namespace Freemium;

use Freemium\Gateways\Bogus;
use Freemium\SubscriptionPlan;
use Freemium\Gateways\GatewayInterface;

class Freemium
{
    public static $daysFreeTrial = 0;

    public static $daysGrace = 3;

    protected static $expiredPlanKey = 'free';

    protected static $expiredPlan;

    public static function getGateway(): GatewayInterface
    {
        return new Bogus();
    }

    public static function getExpiredPlan(): SubscriptionPlan
    {
        return static::$expiredPlan;
    }

    public static function setExpiredPlan(SubscriptionPlan $plan)
    {
        static::$expiredPlan = $plan;
    }

    public static function setExpiredPlanKey(string $key)
    {
        static::$expiredPlanKey = $key;
        static::$expiredPlan = null;
    }
}
