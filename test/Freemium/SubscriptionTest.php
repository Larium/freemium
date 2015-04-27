<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\Base;
use Helper;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    public function setUp()
    {
        Base::mode('test');
        Freemium::$days_free_trial = 0;
    }

    public function testCreateFreeSubscription()
    {
        $sub = $this->build_subscription();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertFalse($sub->getInTrial());
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());

        $changes = $sub->getSubscriptionChanges();
        $this->assert_changed($changes->last(), 'new', null, $this->subscription_plans('free'));
    }

    public function testCreatePaidSubscription()
    {
        Freemium::$days_free_trial = 30;

        $sub = $this->build_subscription([
            'credit_card' => $this->credit_cards('sample'),
            'subscription_plan' => $this->subscription_plans('basic'),
        ]);
        $sub->storeCreditCardOffsite();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertTrue($sub->getInTrial());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertEquals((new DateTime('today'))->modify(Freemium::$days_free_trial.' days'), $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assert_changed($changes->last(), 'new', null, $this->subscription_plans('basic'));
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

        $changes = $sub->getSubscriptionChanges();
        $this->assert_changed($changes->last(), 'upgrade', $this->subscription_plans('free'), $this->subscription_plans('basic'));
    }

    public function testDowngradeToFree()
    {
        $sub = $this->build_subscription([
            'subscription_plan' => $this->subscription_plans('basic'),
            'credit_card' => $this->credit_cards('sample')
        ]);

        $sub->setSubscriptionPlan($this->subscription_plans('free'));

        $this->assertEquals($sub->getStartedOn(), new DateTime('today'));
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());
        $this->assertNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assert_changed($changes->last(), 'downgrade', $this->subscription_plans('basic'), $this->subscription_plans('free'));
    }

    public function testDowngradeToPaid()
    {
        $sub = $this->load_subscription([
            'subscription_plan' => $this->subscription_plans('premium'),
            'credit_card' => $this->credit_cards('sample'),
            'paid_through' => (new DateTime('today'))->modify('+15 days'),
            'in_trial' => false,
            'billing_key' => '1'
        ]);

        $sub->setSubscriptionPlan($this->subscription_plans('basic'));

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->getInTrial());
        $this->assertTrue((new DateTime('today')) < $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assert_changed($changes->last(), 'downgrade', $this->subscription_plans('premium'), $this->subscription_plans('basic'));
    }

    public function testCouponRedemptionCreation()
    {
        $sub = $this->build_subscription([
            'credit_card' => $this->credit_cards('sample'),
            'subscription_plan' => $this->subscription_plans('basic'),
            'in_trial' => false
        ]);

        $coupon = $this->coupons('sample');
        $sub->setCoupon($coupon);

        $couponRedemption = $sub->getCouponRedemption();

        $this->assertInstanceOf('Freemium\\CouponRedemption', $couponRedemption);
    }

    public function testRemainingAmount()
    {
         $sub = $this->build_subscription([
            'subscription_plan' => $this->subscription_plans('premium'),
            'in_trial' => false
        ]);
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
