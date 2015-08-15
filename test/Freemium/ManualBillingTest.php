<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\Base;

class ManualBillingTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    public function setUp()
    {
        Base::mode('test');
        Freemium::$days_free_trial = 0;
    }

    public function testChargePaidSubscription()
    {
        $sub = $this->load_subscription([
            'subscription_plan' => $this->subscription_plans('premium'),
            'in_trial' => false,
            'started_on' => new DateTime('30 days ago'),
            'paid_through' => new DateTime('today'),
            'billing_key' => 1
        ]);
        $sub->attach(new Observer\SubscriptionObserver());
        $sub->storeCreditCardOffsite();

        $bill = new ManualBilling($sub);
        $transaction = $bill->charge();

        # Test that is set the paid throught date.
        $this->assertNotNull($sub->getPaidThrough());
        # After billing no more trial period.
        $this->assertFalse($sub->getInTrial());
        # Test that paid through date is the right one.
        $this->assertEquals((new DateTime('today'))->modify('1 year'), $sub->getPaidThrough());
        # Test that subscription has a billing key from remote system.
        $this->assertNotNull($sub->getBillingKey());
        # Test that billing system charged the correct installment amount.
        $this->assertEquals($transaction->getAmount(), $sub->rate());
        $this->assertFalse($sub->getTransactions()->isEmpty());
    }

    public function testSetToExpire()
    {
        $sub = $this->load_subscription([
            'subscription_plan' => $this->subscription_plans('premium'),
            'in_trial' => false,
            'started_on' => new DateTime('30 days ago'),
            'paid_through' => new DateTime('today'),
            'billing_key' => 0
        ]);
        $sub->attach(new Observer\SubscriptionObserver());

        $subs = array($sub);

        ManualBilling::runBilling($subs);

        $this->assertNotNull($sub->getExpireOn());
        $this->assertEquals((new DateTime('today'))->modify('+ '.Freemium::$days_grace.' days'), $sub->getExpireOn());
        $this->assertEquals(Freemium::$days_grace, $sub->getRemainingDaysOfGrace());
        $this->assertTrue($sub->isInGrace());
    }

    public function testExpiration()
    {
        $free = $this->subscription_plans('free');
        Freemium::setExpiredPlan($free);

        $sub = $this->load_subscription([
            'subscription_plan' => $this->subscription_plans('premium'),
            'in_trial' => false,
            'started_on' => new DateTime('30 days ago'),
            'paid_through' => new DateTime('yesterday'),
            'expire_on' => new DateTime('today'),
            'billing_key' => 0
        ]);
        $sub->attach(new Observer\SubscriptionObserver());

        $subs = array($sub);

        ManualBilling::runBilling($subs);

        $this->assertNotNull($sub->getExpireOn());
        $this->assertTrue($sub->isExpired());
    }
}
