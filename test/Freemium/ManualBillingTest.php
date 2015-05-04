<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\Base;
use Helper;

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
        $sub = $this->build_subscription([
            'subscription_plan' => $this->subscription_plans('basic'),
            'credit_card' => $this->credit_cards('sample'),
        ]);
        $sub->attach(new Observer\SubscriptionObserver());
        $sub->storeCreditCardOffsite();

        $bill = new ManualBilling($sub);
        $transaction = $bill->charge();

        # Test started date for subscription
        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        # Test that is set the paid throught date.
        $this->assertNotNull($sub->getPaidThrough());
        # After billing no more trial period.
        $this->assertFalse($sub->getInTrial());
        # Test that paid through date is the right one.
        $this->assertEquals((new DateTime('today'))->modify('1 month'), $sub->getPaidThrough());
        # Test that subscription has a billing key from remote system.
        $this->assertNotNull($sub->getBillingKey());
        # Test that billing system charged the correct installment amount.
        $this->assertEquals($transaction->getAmount(), $sub->rate());
        $this->assertFalse($sub->getTransactions()->isEmpty());
    }

    public function testExpiration()
    {
        $sub = $this->build_subscription([
            'subscription_plan' => $this->subscription_plans('premium'),
            'in_trial' => false,
            'billing_key' => 0
        ]);
        $sub->attach(new Observer\SubscriptionObserver());

        $bill = new ManualBilling($sub);
        $transaction = $bill->charge();

        $this->assertNotNull($sub->getExpireOn());
        $this->assertEquals(Freemium::$days_grace, $sub->getRemainingDaysOfGrace());
        $this->assertFalse($sub->isInGrace());
    }
}
