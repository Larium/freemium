<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use User;
use DateTime;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Base;
use Helper;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    public function testCreateFreeSubscription()
    {
        $sub = $this->build_subscription();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertFalse($sub->getInTrial());
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());

        $change = end($sub->getSubscriptionChanges());
        $this->assert_changed($change, 'new', null, $this->subscription_plans('free'));
    }

    public function testCreatePaidSubscription()
    {
        Base::mode('test');

        $sub = $this->build_subscription([
            'subscription_plan' => $this->subscription_plans('basic'),
            'credit_card' => $this->credit_cards('sample')
        ]);

        $sub->storeCreditCardOffsite();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertTrue($sub->getInTrial());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertEquals((new DateTime('today'))->modify(Freemium::$days_free_trial.' days'), $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $change = end($sub->getSubscriptionChanges());
        $this->assert_changed($change, 'new', null, $this->subscription_plans('basic'));
    }

    public function testUpgradeFromFree()
    {
        $sub = $this->build_subscription();

        $this->assertFalse($sub->getInTrial());

        $paid_plan = $this->subscription_plans('basic');
        $cc = $this->credit_cards('sample');

        $sub->setCreditCard($cc);
        $sub->setSubscriptionPlan($paid_plan);
        $sub->storeCreditCardOffsite();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->getInTrial());
        $this->assertEquals(new DateTime('today'), $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $change = end($sub->getSubscriptionChanges());
        $this->assert_changed($change, 'upgrade', $this->subscription_plans('free'), $this->subscription_plans('basic'));
    }

    public function testDowngrade()
    {
        $sub = $this->build_subscription([
            'subscription_plan' => $this->subscription_plans('basic'),
            'credit_card' => $this->credit_cards('sample')
        ]);

        $sub->storeCreditCardOffsite();

        $sub->setSubscriptionPlan($this->subscription_plans('free'));
        $this->assertEquals($sub->getStartedOn(), new DateTime('today'));
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());
        $this->assertNull($sub->getBillingKey());

        $change = end($sub->getSubscriptionChanges());
        $this->assert_changed($change, 'downgrade', $this->subscription_plans('basic'), $this->subscription_plans('free'));
    }

    private function assert_changed($change, $reason, $original_plan, $new_plan)
    {
        $this->assertNotNull($change);
        $this->assertEquals($reason, $change->getReason());
        $this->assertEquals($change->getOriginalSubscriptionPlan(), $original_plan);
        $this->assertEquals($change->getNewSubscriptionPlan(), $new_plan);
        $this->assertEquals($change->getOriginalRate(), null === $original_plan ? null : $original_plan->getRate());
        $this->assertEquals($change->getNewRate(), null === $new_plan ? null : $new_plan->getRate());
    }
}
