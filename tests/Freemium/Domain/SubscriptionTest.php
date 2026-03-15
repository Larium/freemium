<?php

namespace Freemium\Domain;

use DateTime;
use DomainException;
use PHPUnit\Framework\TestCase;
use Freemium\Domain\SubscriptionStatus;

class SubscriptionTest extends TestCase
{
    use FixturesHelper;

    public function testSubscriptionHasTokenWithPrefix(): void
    {
        $sub = $this->buildSubscription();
        $this->assertStringStartsWith(Subscription::TOKEN_PREFIX, $sub->getToken());
        $this->assertGreaterThanOrEqual(10, strlen($sub->getToken()));
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
            SubscriptionChangeReason::REASON_NEW,
            null,
            $this->subscriptionPlans('free')
        );
    }

    public function testCreatePaidSubscription()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'days_trial' => 0,
        ]);

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertTrue($sub->isInTrial());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertEquals(
            (new DateTime('today'))->modify($sub->getDaysTrial() . ' days'),
            $sub->getPaidThrough()
        );

        $this->assertTrue($sub->isPaid());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeReason::REASON_NEW,
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

        $sub->setSubscriptionPlan($paid_plan);

        $this->assertEquals(new DateTime('today'), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->isInTrial());
        $this->assertEquals(new DateTime('today'), $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeReason::REASON_UPGRADE,
            $this->subscriptionPlans('free'),
            $this->subscriptionPlans('basic')
        );
    }

    public function testDowngradeToFree()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic')
        ]);

        $sub->setSubscriptionPlan($this->subscriptionPlans('free'));

        $this->assertEquals($sub->getStartedOn(), new DateTime('today'));
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeReason::REASON_DOWNGRADE,
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

        $changes = $sub->getSubscriptionChanges();
        $this->assertChanged(
            end($changes),
            SubscriptionChangeReason::REASON_DOWNGRADE,
            $this->subscriptionPlans('premium'),
            $this->subscriptionPlans('basic')
        );
    }

    public function testNewSubscriptionPaidPlanWithoutBillingKey()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Can not create paid subscription without a billing key.');

        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'subscribable' => $this->users('sue')
        ]);
    }

    public function testCouponRedemptionCreation()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'in_trial' => false
        ]);

        $coupon = $this->coupons('sample');
        $sub->applyCoupon($coupon, $this->generateRedemptionToken());

        $couponRedemption = $sub->getCouponRedemption();

        $this->assertTrue($couponRedemption->isActive());
    }

    public function testMultipleCouponRedemptionCreation()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'in_trial' => false
        ]);

        $sample = $this->coupons('sample');
        $fifteen_percent = $this->coupons('fifteen_percent');
        $sub->applyCoupon($sample, $this->generateRedemptionToken());
        $sub->applyCoupon($fifteen_percent, $this->generateRedemptionToken());

        $couponRedemption = $sub->getCouponRedemption();

        $this->assertTrue($couponRedemption->isActive());
        $this->assertEquals($fifteen_percent, $couponRedemption->getCoupon());
    }

    public function testRemainingAmountForYearlyPlan()
    {
        $sub = $this->subscriptions('testRemainingAmountForYearlyPlan');
        $this->assertNull($sub->getOriginalPlan());
        $this->assertTrue($sub->remainingAmount()->equals(Money::zero('USD')), 'New subscription has no original plan so remaining amount is zero');
    }

    public function testRemainingAmountForMonthlyPlan()
    {
        $sub = $this->subscriptions('testRemainingAmountForMonthlyPlan');
        $this->assertNull($sub->getOriginalPlan());
        $this->assertTrue($sub->remainingAmount()->equals(Money::zero('USD')), 'New subscription has no original plan so remaining amount is zero');
    }

    public function testRemainingDaysOfExpiredSubscription()
    {
        $subscription = $this->subscriptions('testExpiration');

        $remainingDays = $subscription->getRemainingDaysOfGrace();

        $this->assertEquals(0, $remainingDays);
    }

    public function testStartedOnUsesInjectedClock(): void
    {
        $fixed = new \DateTimeImmutable('2025-06-15 12:00:00');
        $clock = new FrozenClock($fixed);
        $sub = $this->buildSubscription(['clock' => $clock]);

        $startedOn = $sub->getStartedOn();
        $expected = DateTime::createFromImmutable($fixed);

        $this->assertEquals($expected->format('Y-m-d'), $startedOn->format('Y-m-d'));
    }

    public function testGetRemainingDaysWithFixedClock(): void
    {
        $fixed = new \DateTimeImmutable('2025-01-01 00:00:00');
        $clock = new FrozenClock($fixed);
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'clock' => $clock,
        ]);

        $remaining = $sub->getRemainingDays();
        $this->assertIsInt($remaining);
        $this->assertGreaterThanOrEqual(0, $remaining, 'New paid subscription paidThrough is today or in future');
        $this->assertSame($remaining, $sub->getRemainingDays(), 'getRemainingDays is deterministic with fixed clock');
    }

    public function testIsExpiredWithFixedClockWhenExpireOnIsToday(): void
    {
        $fixed = new \DateTimeImmutable('2025-01-15 00:00:00');
        $clock = new FrozenClock($fixed);
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'clock' => $clock,
        ]);
        $sub->expireNow();
        $this->assertNotNull($sub->getExpireOn());
        $this->assertEquals($fixed->format('Y-m-d'), $sub->getExpireOn()->format('Y-m-d'));
        $this->assertSame(SubscriptionStatus::CANCELED, $sub->getStatus());

        $reflection = new \ReflectionClass($sub);
        $paidThroughProp = $reflection->getProperty('paidThrough');
        $paidThroughProp->setAccessible(true);
        $paidThroughProp->setValue($sub, DateTime::createFromImmutable($fixed->modify('-1 day')));
        $expireOnProp = $reflection->getProperty('expireOn');
        $expireOnProp->setAccessible(true);
        $expireOnProp->setValue($sub, DateTime::createFromImmutable($fixed));

        $this->assertTrue($sub->isExpired());
    }

    public function testBillingAmountDefaultDateUsesClock(): void
    {
        $fixed = new \DateTimeImmutable('2025-03-10 00:00:00');
        $clock = new FrozenClock($fixed);
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'clock' => $clock,
        ]);

        $amountWithoutArg = $sub->billingAmount();
        $amountWithExplicitDate = $sub->billingAmount(DateTime::createFromImmutable($fixed));

        $this->assertTrue($amountWithoutArg->equals($amountWithExplicitDate));
    }

    /**
     * Plan change preserves remaining monetary value: premium (yearly) -> basic (monthly).
     * 15 days left on premium should convert to fewer days on basic (basic is more expensive per day).
     */
    public function testPlanChangePreservesValuePremiumToBasic(): void
    {
        $sub = $this->subscriptions('testDowngradeToPaid');
        $this->assertSame(15, $sub->getRemainingDays());

        $basic = $this->subscriptionPlans('basic');
        $sub->setSubscriptionPlan($basic);

        $this->assertSame($this->subscriptionPlans('basic'), $sub->getSubscriptionPlan());
        $this->assertSame($this->subscriptionPlans('premium'), $sub->getOriginalPlan());
        $remainingDays = $sub->getRemainingDays();
        $this->assertLessThan(15, $remainingDays, 'Downgrade to more expensive per-day plan should yield fewer days');
        $this->assertGreaterThanOrEqual(1, $remainingDays);

        $rateCalculator = new RateCalculator();
        $oldValue = $rateCalculator->dailyRate($this->subscriptionPlans('premium'))->multiply((string) 15);
        $newDaily = $rateCalculator->dailyRate($basic);
        $expectedDaysMin = (int) floor((float) $oldValue->getMinorAmount() / (float) $newDaily->getMinorAmount());
        $this->assertGreaterThanOrEqual($expectedDaysMin, $remainingDays);
        $this->assertLessThanOrEqual($expectedDaysMin + 1, $remainingDays);
    }

    /**
     * Plan change preserves remaining monetary value: basic (monthly) -> premium (yearly).
     * 15 days left on basic should convert to more days on premium (premium is cheaper per day).
     */
    public function testPlanChangePreservesValueBasicToPremium(): void
    {
        $sub = $this->subscriptions('testRemainingAmountForMonthlyPlan');
        $this->assertSame(15, $sub->getRemainingDays());

        $premium = $this->subscriptionPlans('premium');
        $sub->setSubscriptionPlan($premium);

        $this->assertSame($this->subscriptionPlans('premium'), $sub->getSubscriptionPlan());
        $this->assertSame($this->subscriptionPlans('basic'), $sub->getOriginalPlan());
        $remainingDays = $sub->getRemainingDays();
        $this->assertGreaterThan(15, $remainingDays, 'Upgrade to cheaper per-day plan should yield more days');
        $this->assertLessThan(365, $remainingDays);

        $rateCalculator = new RateCalculator();
        $oldValue = $rateCalculator->dailyRate($this->subscriptionPlans('basic'))->multiply((string) 15);
        $newDaily = $rateCalculator->dailyRate($premium);
        $expectedDaysMin = (int) floor((float) $oldValue->getMinorAmount() / (float) $newDaily->getMinorAmount());
        $this->assertGreaterThanOrEqual($expectedDaysMin, $remainingDays);
        $this->assertLessThanOrEqual($expectedDaysMin + 1, $remainingDays);
    }

    private function assertChanged(SubscriptionChange $change, $reason, $original_plan, $new_plan)
    {
        $this->assertNotNull($change);
        $this->assertEquals($reason, $change->getReason());
        $this->assertEquals($change->getOriginalSubscriptionPlan(), $original_plan);
        $this->assertEquals($change->getNewSubscriptionPlan(), $new_plan);
        $expectedOriginal = null === $original_plan ? Money::zero($change->getNewRate()->getCurrency()) : $original_plan->getRate();
        $this->assertTrue($change->getOriginalRate()->equals($expectedOriginal));
        $this->assertTrue($change->getNewRate()->equals($new_plan->getRate()));
    }
}
