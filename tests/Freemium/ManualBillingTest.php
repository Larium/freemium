<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\Base;

class ManualBillingTest extends \PHPUnit_Framework_TestCase
{
    use FixturesHelper;

    public function testChargePaidSubscription()
    {
        $sub = $this->subscriptions('testChargePaidSubscription');

        $sub->attach(new Observer\SubscriptionObserver());
        $sub->storeCreditCardOffsite();

        $bill = new ManualBilling($sub);
        $transaction = $bill->charge();

        # Test that is set the paid throught date.
        $this->assertNotNull($sub->getPaidThrough());
        # After billing no more trial period.
        $this->assertFalse($sub->isInTrial());
        # Test that paid through date is the right one.
        $this->assertEquals((new DateTime('today'))->modify('1 year'), $sub->getPaidThrough());
        # Test that subscription has a billing key from remote system.
        $this->assertNotNull($sub->getBillingKey());
        # Test that billing system charged the correct installment amount.
        $this->assertEquals($transaction->getAmount(), $sub->rate());
        $this->assertNotEmpty($sub->getTransactions());
    }

    public function testSetToExpire()
    {
        $sub = $this->subscriptions('testSetToExpire');

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
        $free = $this->subscriptionPlans('free');
        Freemium::setExpiredPlan($free);

        $sub = $this->subscriptions('testExpiration');

        $sub->attach(new Observer\SubscriptionObserver());

        $subs = array($sub);

        ManualBilling::runBilling($subs);

        $this->assertNotNull($sub->getExpireOn());
        $this->assertTrue($sub->isExpired());
    }
}
