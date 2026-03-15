<?php

namespace Freemium\Domain;

use AktiveMerchant\Billing\Base;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Freemium\Infrastructure\Service\CustomIdGenerator;

trait FixturesHelper
{
    private $objects = [];

    private ?IdGenerator $idGenerator = null;

    protected function setUp(): void
    {
        Base::mode('test');
        $this->idGenerator = new CustomIdGenerator();
        $this->fixturesSetUp();
    }

    protected function buildSubscription(array $options = []): Subscription
    {
        $default = [
            'subscribable' => $this->users('bob'),
            'subscription_plan' => $this->subscriptionPlans('free'),
        ];

        $params = array_merge($default, $options);

        $token = $params['token'] ?? $this->idGenerator->generate(Subscription::TOKEN_PREFIX);
        unset($params['token']);

        $clock = $params['clock'] ?? null;
        unset($params['clock']);
        $daysTrial = $params['days_trial'] ?? 0;
        $daysGrace = $params['days_grace'] ?? 0;
        unset($params['days_trial'], $params['days_grace']);

        $sub = new Subscription(
            $token,
            $params['subscribable'],
            $params['subscription_plan'],
            $clock,
            $daysTrial,
            $daysGrace
        );

        unset($params['subscription_plan']);

        return $sub;
    }

    protected function generateSubscriptionToken(): string
    {
        return $this->idGenerator->generate(Subscription::TOKEN_PREFIX);
    }

    protected function generateRedemptionToken(): string
    {
        return $this->idGenerator->generate(CouponRedemption::TOKEN_PREFIX);
    }

    protected function generateCouponToken(): string
    {
        return $this->idGenerator->generate(Coupon::TOKEN_PREFIX);
    }

    protected function generatePlanToken(): string
    {
        return $this->idGenerator->generate(SubscriptionPlan::TOKEN_PREFIX);
    }

    protected function getIdGenerator(): IdGenerator
    {
        return $this->idGenerator;
    }

    protected function subscriptionPlans($key): SubscriptionPlan
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function subscriptions($key): Subscription
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function coupons($key): Coupon
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function users($key): User
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function creditCards($key)
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    private function fixturesSetUp(): void
    {
        $loader = new class () extends NativeLoader {
            protected function createPropertyAccessor(): PropertyAccessorInterface
            {
                return new ReflectionPropertyAccessor(parent::createPropertyAccessor());
            }
        };
        $this->objects['discount'] = $loader->loadFile(__DIR__ . '/../../fixtures/discount.yml');
        $this->objects['creditCards'] = $loader->loadFile(__DIR__ . '/../../fixtures/credit_cards.php', $this->objects['discount']->getParameters(), $this->objects['discount']->getObjects());
        $this->objects['users'] = $loader->loadFile(__DIR__ . '/../../fixtures/users.yml', $this->objects['creditCards']->getParameters(), $this->objects['creditCards']->getObjects());
        $this->objects['subscriptionPlans'] = $loader->loadFile(__DIR__ . '/../../fixtures/subscription_plans.yml', $this->objects['users']->getParameters(), $this->objects['users']->getObjects());
        $this->objects['coupons'] = $loader->loadFile(__DIR__ . '/../../fixtures/coupons.yml', $this->objects['subscriptionPlans']->getParameters(), $this->objects['subscriptionPlans']->getObjects());
        $this->objects['subscriptions'] = $loader->loadFile(__DIR__ . '/../../fixtures/subscriptions.php', $this->objects['coupons']->getParameters(), $this->objects['coupons']->getObjects());
    }

    protected function tearDown(): void
    {
        $this->objects = [];
    }
}
