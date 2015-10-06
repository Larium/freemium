<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Symfony\Component\Yaml\Parser;
use AktiveMerchant\Billing\CreditCard;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

trait Helper
{
    protected $em;

    public function build_subscription(array $options = array())
    {
        $default = array(
            'subscribable' => $this->users('bob'),
            'subscription_plan' => $this->subscription_plans('free'),
        );

        $params = array_merge($default, $options);

        $sub = new \Model\Subscription();

        $sub->setSubscriptionPlan($params['subscription_plan']);
        unset($params['subscription_plan']);

        if (isset($params['credit_card'])) {
            $sub->setCreditCard($params['credit_card']);
            unset($params['credit_card']);
        }

        $this->hydrateModel($sub, $params);

        return $sub;
    }

    public function load_subscription(array $options = array())
    {
        $default = array(
            'subscription_plan' => $this->subscription_plans('free'),
            'subscribable' => $this->users('bob'),
        );

        $params = array_merge($default, $options);

        $params['rate'] = $params['subscription_plan']->getRate();

        $sub = new \Model\Subscription();

        $this->hydrateModel($sub, $params);

        return $sub;
    }

    public function build_subscription_plan(array $options)
    {
        $plan = new \Model\SubscriptionPlan();

        $this->hydrateModel($plan, $options);

        return $plan;
    }

    public function users($key)
    {
        $params = $this->fetch(__FUNCTION__, $key);

        $user = new \Model\User();

        $this->hydrateModel($user, $params);

        return $user;

    }

    public function coupons($key)
    {
        $params = $this->fetch(__FUNCTION__, $key);

        $coupon = new \Model\Coupon();

        $this->hydrateModel($coupon, $params);

        return $coupon;
    }

    public function subscription_plans($key)
    {
        $params = $this->fetch(__FUNCTION__, $key);

        $plan = new \Model\SubscriptionPlan();

        $this->hydrateModel($plan, $params);

        return $plan;
    }

    public function credit_cards($key)
    {
        $credit_cards = include __DIR__ . '/../fixtures/credit_cards.php';

        $params = $credit_cards[$key];

        return new CreditCard($params);
    }

    private function fetch($method, $key)
    {
        $yaml = new Parser();
        $data = $yaml->parse(file_get_contents(__DIR__ . "/../fixtures/{$method}.yml"));

        return $data[$key];
    }

    public function setUpEntityManager()
    {
        $params = include __DIR__ . '/../../config/parameters.php';
        $paths = array(__DIR__ . '/../../src/Model', __DIR__ . '/../../config/metadata');
        $isDevMode = true;

        // the connection configuration
        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => $params['database']['test']['username'],
            'password' => $params['database']['test']['password'],
            'dbname'   => $params['database']['test']['database'],
        );

        $config = Setup::createYAMLMetadataConfiguration($paths, $isDevMode);
        $this->em = EntityManager::create($dbParams, $config);
    }

    private function hydrateModel($instance, $params)
    {
        $reflection = new \ReflectionClass($instance);

        foreach ($params as $key => $value) {
            $property = $reflection->getProperty($key);
            $property->setAccessible(true);
            $property->setValue($instance, $value);
            $property->setAccessible(false);
        }
    }
}
