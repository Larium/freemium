<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use AktiveMerchant\Billing\Base;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    use FixturesHelper;

    public function testSubscriptionWithoutPlan()
    {
        $sub = new Subscription($this->users('bob'));

        $this->assertNull($sub->rate());

        $this->assertNull($sub->getCouponRedemption());

        $coupon = $this->coupons('sample');
        $coupon->addSubscriptionPlan($this->subscriptionPlans('basic'));
        $this->assertFalse($sub->applyCoupon($coupon));
        $this->assertNull($sub->getCoupon());
    }

    public function testCreateFreeSubscription()
    {
        $sub = $this->buildSubscription();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertFalse($sub->isInTrial());
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeInterface::REASON_NEW,
            null,
            $this->subscriptionPlans('free')
        );
    }

    public function testCreatePaidSubscription()
    {
        Freemium::$days_free_trial = 0;

        $sub = $this->buildSubscription([
            'credit_card' => $this->creditCards('bogus_card'),
            'subscription_plan' => $this->subscriptionPlans('basic'),
        ]);
        $sub->storeCreditCardOffsite();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertTrue($sub->isInTrial());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertEquals(
            (new DateTime('today'))->modify(Freemium::$days_free_trial.' days'),
            $sub->getPaidThrough()
        );

        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeInterface::REASON_NEW,
            null,
            $this->subscriptionPlans('basic')
        );
    }

    public function testUpgradeFromFree()
    {
        $sub = $this->buildSubscription();

        $this->assertFalse($sub->isInTrial());

        $paid_plan = $this->subscriptionPlans('basic');
        $cc = $this->creditCards('bogus_card');

        $sub->setCreditCard($cc);
        $sub->setSubscriptionPlan($paid_plan);
        $sub->storeCreditCardOffsite();

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->isInTrial());
        $this->assertEquals(new DateTime('today'), $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeInterface::REASON_UPGRADE,
            $this->subscriptionPlans('free'),
            $this->subscriptionPlans('basic')
        );
    }

    public function testDowngradeToFree()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'credit_card' => $this->creditCards('bogus_card')
        ]);

        $sub->setSubscriptionPlan($this->subscriptionPlans('free'));

        $this->assertEquals($sub->getStartedOn(), new DateTime('today'));
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());
        $this->assertNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeInterface::REASON_DOWNGRADE,
            $this->subscriptionPlans('basic'),
            $this->subscriptionPlans('free')
        );
    }

    public function testDowngradeToPaid()
    {
        $sub = $this->subscriptions('testDowngradeToPaid');

        $sub->setSubscriptionPlan($this->subscriptionPlans('basic'));

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->isInTrial());
        $this->assertTrue((new DateTime('today')) < $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($sub->getBillingKey());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeInterface::REASON_DOWNGRADE,
            $this->subscriptionPlans('premium'),
            $this->subscriptionPlans('basic')
        );
    }

    public function testCouponRedemptionCreation()
    {
        $sub = $this->buildSubscription([
            'credit_card' => $this->creditCards('bogus_card'),
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'in_trial' => false
        ]);

        $coupon = $this->coupons('sample');
        $sub->applyCoupon($coupon);

        $couponRedemption = $sub->getCouponRedemption();

        $this->assertTrue($couponRedemption->isActive());
    }

    public function testMultipleCouponRedemptionCreation()
    {
        $sub = $this->buildSubscription([
            'credit_card' => $this->creditCards('bogus_card'),
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'in_trial' => false
        ]);

        $sample = $this->coupons('sample');
        $fifteen_percent = $this->coupons('fifteen_percent');
        $sub->applyCoupon($sample);
        $sub->applyCoupon($fifteen_percent);

        $couponRedemption = $sub->getCouponRedemption();

        $this->assertTrue($couponRedemption->isActive());
        $this->assertEquals($fifteen_percent, $couponRedemption->getCoupon());
    }

    public function testRemainingAmountForYearlyPlan()
    {
        $sub = $this->subscriptions('testRemainingAmountForYearlyPlan');

        $premiumYearlyAmount = 2495;
        $premiumDailyAmount = round(2495 / 365); #6.835616438 rounds to 7
        $premiumDaysRemaing = 15;

        $this->assertEquals(
            $premiumDailyAmount * $premiumDaysRemaing,
            $sub->remainingAmount()
        );
    }

    public function testRemainingAmountForMonthlyPlan()
    {
        $sub = $this->subscriptions('testRemainingAmountForMonthlyPlan');

        $basicYearlyAmount = 1295;
        $basicDailyAmount = round((1295 * 12) / 365); #42.575342466 rounds to 43
        $basicDaysRemaing = 15;

        $this->assertEquals(
            $basicDailyAmount * $basicDaysRemaing,
            $sub->remainingAmount()
        );
    }

    public function testSubscriptionObservers()
    {
        $sub = new Subscription($this->users('bob'));
        $observer = new Observer\SubscriptionObserver();
        $sub->attach($observer);

        $this->assertEquals(1, count($sub->getObservers()));
        $sub->detach($observer);
        $this->assertEquals(0, count($sub->getObservers()));
    }

    private function assertChanged($change, $reason, $original_plan, $new_plan)
    {
        $this->assertNotNull($change);
        $this->assertEquals($reason, $change->getReason());
        $this->assertEquals($change->getOriginalSubscriptionPlan(), $original_plan);
        $this->assertEquals($change->getNewSubscriptionPlan(), $new_plan);
        $this->assertEquals($change->getOriginalRate(), null === $original_plan ? null : $original_plan->getRate());
        $this->assertEquals($change->getNewRate(), null === $new_plan ? null : $new_plan->getRate());
    }
}
