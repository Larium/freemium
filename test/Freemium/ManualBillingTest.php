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
        $sub->storeCreditCardOffsite();

        $bill = new ManualBilling($sub);
        $transaction = $bill->charge();

        print_r($transaction);

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->getInTrial());
        $this->assertEquals((new DateTime('today'))->modify('1 month'), $sub->getPaidThrough());
        $this->assertNotNull($sub->getBillingKey());

        $this->assertEquals($transaction->getAmount(), $sub->rate());
    }
}
