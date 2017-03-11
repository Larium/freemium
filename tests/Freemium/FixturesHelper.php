<?php

namespace Freemium;

use Nelmio\Alice\Fixtures\Loader;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Base;

trait FixturesHelper
{
    private $objects = [];

    protected function setUp()
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

        if (isset($params['credit_card'])) {
            $sub->setCreditCard($params['credit_card']);
        }

        return $sub;
    }

    protected function subscriptionPlans($key)
    {
        return $this->objects[__FUNCTION__][$key];
    }

    protected function subscriptions($key)
    {
        return $this->objects[__FUNCTION__][$key];
    }

    protected function coupons($key)
    {
        return $this->objects[__FUNCTION__][$key];
    }

    protected function users($key)
    {
        return $this->objects[__FUNCTION__][$key];
    }

    protected function creditCards($key)
    {
        return $this->objects[__FUNCTION__][$key];
    }

    private function fixturesSetUp()
    {
        $loader = new Loader();
        $this->objects['discount'] = $loader->load(__DIR__.'/../fixtures/discount.yml');
        $this->objects['creditCards'] = $loader->load(__DIR__.'/../fixtures/credit_cards.php');
        $this->objects['users'] = $loader->load(__DIR__.'/../fixtures/users.yml');
        $this->objects['subscriptionPlans'] = $loader->load(__DIR__.'/../fixtures/subscription_plans.yml');
        $this->objects['coupons'] = $loader->load(__DIR__.'/../fixtures/coupons.yml');
        $this->objects['subscriptions'] = $loader->load(__DIR__.'/../fixtures/subscriptions.php');
    }

    protected function tearDown()
    {
        $this->objects = [];
    }
}
