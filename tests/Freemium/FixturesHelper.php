<?php

namespace Freemium;

use AktiveMerchant\Billing\Base;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

trait FixturesHelper
{
    private $objects = [];

    protected function setUp(): void
    {
        Base::mode('test');
        Freemium::$days_free_trial = 0;
        Freemium::setExpiredPlanKey('free');
        $this->fixturesSetUp();
    }

    protected function buildSubscription(array $options = array())
    {
        $default = array(
            'subscribable' => $this->users('bob'),
            'subscription_plan' => $this->subscriptionPlans('free'),
        );

        $params = array_merge($default, $options);

        $sub = new Subscription(
            $params['subscribable'],
            $params['subscription_plan']
        );

        unset($params['subscription_plan']);

        return $sub;
    }

    protected function subscriptionPlans($key)
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function subscriptions($key)
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function coupons($key)
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function users($key)
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    protected function creditCards($key)
    {
        return $this->objects[__FUNCTION__]->getObjects()[$key];
    }

    private function fixturesSetUp()
    {
        $loader = new class extends NativeLoader {
            protected function createPropertyAccessor(): PropertyAccessorInterface
            {
                return new ReflectionPropertyAccessor(parent::createPropertyAccessor());
            }
        };
        $this->objects['discount'] = $loader->loadFile(__DIR__.'/../fixtures/discount.yml');
        $this->objects['creditCards'] = $loader->loadFile(__DIR__.'/../fixtures/credit_cards.php', $this->objects['discount']->getParameters(), $this->objects['discount']->getObjects());
        $this->objects['users'] = $loader->loadFile(__DIR__.'/../fixtures/users.yml', $this->objects['creditCards']->getParameters(), $this->objects['creditCards']->getObjects());
        $this->objects['subscriptionPlans'] = $loader->loadFile(__DIR__.'/../fixtures/subscription_plans.yml', $this->objects['users']->getParameters(), $this->objects['users']->getObjects());
        $this->objects['coupons'] = $loader->loadFile(__DIR__.'/../fixtures/coupons.yml', $this->objects['subscriptionPlans']->getParameters(), $this->objects['subscriptionPlans']->getObjects());
        $this->objects['subscriptions'] = $loader->loadFile(__DIR__.'/../fixtures/subscriptions.php', $this->objects['coupons']->getParameters(), $this->objects['coupons']->getObjects());
    }

    protected function tearDown(): void
    {
        $this->objects = [];
    }
}
