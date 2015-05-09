<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use Symfony\Component\Yaml\Parser;
use AktiveMerchant\Billing\CreditCard;

trait Helper
{
    public function build_subscription(array $options = array())
    {
        $default = array(
            'subscription_plan' => $this->subscription_plans('free'),
            'subscribable' => $this->users('bob')
        );

        $params = array_merge($default, $options);

        $sub = new Freemium\Subscription();

        $sub->bindProperties($params);

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

        $sub = new Freemium\Subscription();

        $sub->setProperties($params);

        return $sub;
    }

    public function users($key)
    {
        $params = $this->fetch(__FUNCTION__, $key);

        $user = new User();

        $user->setProperties($params);

        return $user;

    }

    public function coupons($key)
    {
        $params = $this->fetch(__FUNCTION__, $key);

        $coupon = new Freemium\Coupon();

        $coupon->setProperties($params);

        return $coupon;
    }

    public function subscription_plans($key)
    {
        $params = $this->fetch(__FUNCTION__, $key);

        $plan = new Freemium\SubscriptionPlan();

        $plan->setProperties($params);

        return $plan;
    }

    public function credit_cards($key)
    {
        $credit_cards = include __DIR__ . '/fixtures/credit_cards.php';

        $params = $credit_cards[$key];

        return new CreditCard($params);
    }

    private function fetch($method, $key)
    {
        $yaml = new Parser();
        $data = $yaml->parse(file_get_contents(__DIR__ . "/fixtures/{$method}.yml"));

        return $data[$key];
    }
}
