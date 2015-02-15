<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use User;
use DateTime;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Base;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFreeSubscription()
    {
        $user = new User();
        $user->setProperties(['name' => 'Bob', 'email' => 'bob@example.com']);
        $plan = new SubscriptionPlan();
        $plan->setProperties(['name' => 'free', 'rate_cents' => 0]);

        $sub = new Subscription();
        $sub->setSubscribable($user);
        $sub->setSubscriptionPlan($plan);

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());

        $this->assertFalse($sub->getInTrial());

        $this->assertNull($sub->getPaidThrough());

        $this->assertFalse($sub->isPaid());

    }

    public function testCreatePaidSubscription()
    {
        Base::mode('test');

        $user = new User();
        $user->setProperties(['name' => 'Bob', 'email' => 'bob@example.com']);

        $plan = new SubscriptionPlan();
        $plan->setProperties(['name' => 'basic', 'rate_cents' => 1295, 'cycle' => SubscriptionPlan::MONTHLY]);

        $cc = new CreditCard([
            'first_name' => 'Bob',
            'last_name'  => 'Smith',
            'month'      => '12',
            'year'       => date('Y', strtotime('1 year')),
            'type'       => 'bogus',
            'number'     => '1'
        ]);

        $sub = new Subscription();
        $sub->setSubscribable($user);
        $sub->setSubscriptionPlan($plan);
        $sub->setCreditCard($cc);


        $sub->storeCreditCardOffsite();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());

        $this->assertTrue($sub->getInTrial());

        $this->assertNotNull($sub->getPaidThrough());

        $this->assertEquals((new DateTime('today'))->modify(Freemium::$days_free_trial.' days'), $sub->getPaidThrough());

        $this->assertTrue($sub->isPaid());

        $this->assertNotNull($sub->getBillingKey());
    }

    public function testUpgradeFromFree()
    {
        $user = new User();
        $user->setProperties(['name' => 'Bob', 'email' => 'bob@example.com']);
        $plan = new SubscriptionPlan();
        $plan->setProperties(['name' => 'free', 'rate_cents' => 0]);

        $sub = new Subscription();
        $sub->setSubscribable($user);
        $sub->setSubscriptionPlan($plan);

        $this->assertFalse($sub->getInTrial());

        $paid_plan = new SubscriptionPlan();
        $paid_plan->setProperties(['name' => 'basic', 'rate_cents' => 1295, 'cycle' => SubscriptionPlan::MONTHLY]);
        $cc = new CreditCard([
            'first_name' => 'Bob',
            'last_name'  => 'Smith',
            'month'      => '12',
            'year'       => date('Y', strtotime('1 year')),
            'type'       => 'bogus',
            'number'     => '1'
        ]);

        $sub->setCreditCard($cc);
        $sub->setSubscriptionPlan($paid_plan);
        $sub->storeCreditCardOffsite();
    }
}
