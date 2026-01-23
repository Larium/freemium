<?php

namespace Freemium;

use Freemium\Domain\Gateways\Bogus;
use Freemium\Domain\SubscriptionPlan;
use Freemium\Domain\Gateways\GatewayInterface;

class Freemium
{
    public static int $daysFreeTrial = 0;

    public static int $daysGrace = 3;

    protected static string$expiredPlanKey = 'free';

    protected static ?SubscriptionPlan $expiredPlan = null;

    public static function getGateway(): GatewayInterface
    {
        return new Bogus();
    }

    public static function getExpiredPlan(): ?SubscriptionPlan
    {
        return static::$expiredPlan;
    }

    public static function setExpiredPlan(SubscriptionPlan $plan): void
    {
        static::$expiredPlan = $plan;
    }

    public static function setExpiredPlanKey(string $key): void
    {
        static::$expiredPlanKey = $key;
        static::$expiredPlan = null;
    }
}
