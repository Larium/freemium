<?php

namespace Freemium\Domain;

use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

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

        $this->assertEquals($this->today(), $sub->getStartedOn());
        $this->assertFalse($sub->isInTrial());
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());

        $change = new SubscriptionChange($sub, SubscriptionChangeReason::REASON_NEW, null);
        $this->assertChanged(
            $change,
            SubscriptionChangeReason::REASON_NEW,
            null,
            $this->subscriptionPlans('free')
        );
    }

    public function testCreatePaidSubscription()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
        ]);

        $this->assertEquals($this->today(), $sub->getStartedOn());
        $this->assertTrue($sub->isInTrial());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertEquals(
            $this->today()->modify($sub->getSubscriptionPlan()->getTrialDays() . ' days'),
            $sub->getPaidThrough()
        );
        $this->assertEquals($this->today(), $sub->getTrialStartedOn());
        $this->assertEquals($sub->getPaidThrough(), $sub->getTrialEndsOn());

        $this->assertTrue($sub->isPaid());

        $change = new SubscriptionChange($sub, SubscriptionChangeReason::REASON_NEW, null);
        $this->assertChanged(
            $change,
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

        $change = $sub->setSubscriptionPlan($paid_plan, $this->today());

        $this->assertEquals($this->today(), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->isInTrial());
        $this->assertEquals($this->today(), $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());
        $this->assertNotNull($change);
        $this->assertChanged(
            $change,
            SubscriptionChangeReason::REASON_UPGRADE,
            $this->subscriptionPlans('free'),
            $this->subscriptionPlans('basic')
        );
    }

    public function testPlanChangesCannotStartOrKeepTrial(): void
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
        ]);

        $this->assertTrue($sub->isInTrial());

        $sub->setSubscriptionPlan($this->subscriptionPlans('premium'), $this->today());

        $this->assertFalse($sub->isInTrial());

        $sub->setSubscriptionPlan($this->subscriptionPlans('free'), $this->today());

        $this->assertFalse($sub->isInTrial());
    }

    public function testDowngradeToFree()
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic')
        ]);

        $change = $sub->setSubscriptionPlan($this->subscriptionPlans('free'), $this->today());

        $this->assertEquals($sub->getStartedOn(), $this->today());
        $this->assertNull($sub->getPaidThrough());
        $this->assertFalse($sub->isPaid());

        $this->assertNotNull($change);
        $this->assertChanged(
            $change,
            SubscriptionChangeReason::REASON_DOWNGRADE,
            $this->subscriptionPlans('basic'),
            $this->subscriptionPlans('free')
        );
    }

    public function testDowngradeToPaid()
    {
        $sub = $this->subscriptions('testDowngradeToPaid');

        $change = $sub->setSubscriptionPlan($this->subscriptionPlans('basic'), $this->today());

        $this->assertEquals($this->today(), $sub->getStartedOn());
        $this->assertNotNull($sub->getPaidThrough());
        $this->assertFalse($sub->isInTrial());
        $this->assertTrue($this->today() < $sub->getPaidThrough());
        $this->assertTrue($sub->isPaid());

        $this->assertNotNull($change);
        $this->assertChanged(
            $change,
            SubscriptionChangeReason::REASON_DOWNGRADE,
            $this->subscriptionPlans('premium'),
            $this->subscriptionPlans('basic')
        );
    }

    public function testNewSubscriptionPaidPlanWithoutBillingKey()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Can not create paid subscription without a billing key.');

        $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'subscribable' => $this->users('sue')
        ]);
    }

    public function testCouponRedemptionCreation()
    {
        $redemptionRepo = new \Freemium\Domain\Repository\CouponRedemptionStubRepository();
        $planRepo = new \Freemium\Domain\Repository\CouponPlanStubRepository();
        $handler = new \Freemium\Application\UseCase\ApplyCoupon\ApplyCouponHandler(
            new \Freemium\Application\Event\EventProvider(),
            $redemptionRepo,
            $planRepo
        );

        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'inTrial' => false,
        ]);

        $coupon = $this->coupons('sample');
        $handler->handle(new \Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon(
            $sub,
            $coupon,
            $this->generateRedemptionToken()
        ));

        $couponRedemption = $redemptionRepo->findBestActiveForSubscription($sub, $this->today());
        $this->assertNotNull($couponRedemption);
        $this->assertTrue($couponRedemption->isActive());
    }

    public function testMultipleCouponRedemptionCreation()
    {
        $redemptionRepo = new \Freemium\Domain\Repository\CouponRedemptionStubRepository();
        $planRepo = new \Freemium\Domain\Repository\CouponPlanStubRepository();
        $handler = new \Freemium\Application\UseCase\ApplyCoupon\ApplyCouponHandler(
            new \Freemium\Application\Event\EventProvider(),
            $redemptionRepo,
            $planRepo
        );

        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'inTrial' => false,
        ]);

        $sample = $this->coupons('sample');
        $fifteen_percent = $this->coupons('fifteen_percent');
        $handler->handle(new \Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon(
            $sub,
            $sample,
            $this->generateRedemptionToken()
        ));
        $handler->handle(new \Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon(
            $sub,
            $fifteen_percent,
            $this->generateRedemptionToken()
        ));

        $couponRedemption = $redemptionRepo->findBestActiveForSubscription($sub, $this->today());
        $this->assertNotNull($couponRedemption);
        $this->assertTrue($couponRedemption->isActive());
        $this->assertEquals($fifteen_percent, $couponRedemption->getCoupon());
    }

    public function testRemainingAmountForYearlyPlan()
    {
        $sub = $this->subscriptions('testRemainingAmountForYearlyPlan');
        $this->assertNull($sub->getOriginalPlan());
        $this->assertTrue($sub->remainingAmount($this->today())->equals(Money::zero('USD')), 'New subscription has no original plan so remaining amount is zero');
    }

    public function testRemainingAmountForMonthlyPlan()
    {
        $sub = $this->subscriptions('testRemainingAmountForMonthlyPlan');
        $this->assertNull($sub->getOriginalPlan());
        $this->assertTrue($sub->remainingAmount($this->today())->equals(Money::zero('USD')), 'New subscription has no original plan so remaining amount is zero');
    }

    public function testRemainingDaysOfExpiredSubscription()
    {
        $subscription = $this->subscriptions('testExpiration');

        $remainingDays = $subscription->getRemainingDaysOfGrace($this->today());

        $this->assertEquals(0, $remainingDays);
    }

    public function testStartedOnUsesExplicitDate(): void
    {
        $fixed = new DateTimeImmutable('2025-06-15 12:00:00');
        $on = $fixed->setTime(0, 0, 0);
        $sub = $this->buildSubscription(['on' => $on]);

        $this->assertEquals($on->format('Y-m-d'), $sub->getStartedOn()->format('Y-m-d'));
    }

    public function testGetRemainingDaysWithFixedDate(): void
    {
        $on = new DateTimeImmutable('2025-01-01 00:00:00');
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'on' => $on,
        ]);

        $remaining = $sub->getRemainingDays($on);
        $this->assertIsInt($remaining);
        $this->assertGreaterThanOrEqual(0, $remaining, 'New paid subscription paidThrough is today or in future');
        $this->assertSame($remaining, $sub->getRemainingDays($on), 'getRemainingDays is deterministic with fixed date');
    }

    public function testIsExpiredWithFixedDateWhenExpireOnIsToday(): void
    {
        $on = new DateTimeImmutable('2025-01-15 00:00:00');
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
            'on' => $on,
        ]);
        $sub->cancel($on);
        $this->assertNotNull($sub->getCancelAt());
        $this->assertEquals($on->format('Y-m-d'), $sub->getCancelAt()->format('Y-m-d'));
        $this->assertSame(SubscriptionStatus::CANCELED, $sub->getStatus());

        $reflection = new \ReflectionClass($sub);
        $paidThroughProp = $reflection->getProperty('paidThrough');
        $paidThroughProp->setAccessible(true);
        $paidThroughProp->setValue($sub, $on->modify('-1 day'));
        $cancelAtProp = $reflection->getProperty('cancelAt');
        $cancelAtProp->setAccessible(true);
        $cancelAtProp->setValue($sub, $on);

        $this->assertTrue($sub->isCancellationDue($on));
    }

    public function testBillingAmountWithAndWithoutCoupon(): void
    {
        $sub = $this->buildSubscription([
            'subscription_plan' => $this->subscriptionPlans('basic'),
        ]);

        $amountWithoutCoupon = $sub->billingAmount();
        $this->assertTrue($amountWithoutCoupon->equals($this->subscriptionPlans('basic')->getRate()));

        $coupon = $this->coupons('sample');
        $amountWithCoupon = $sub->billingAmount($coupon);
        $this->assertTrue($amountWithCoupon->equals($coupon->getDiscount($this->subscriptionPlans('basic')->getRate())));
    }

    /**
     * Plan change preserves remaining monetary value: premium (yearly) -> basic (monthly).
     * 15 days left on premium should convert to fewer days on basic (basic is more expensive per day).
     */
    public function testPlanChangePreservesValuePremiumToBasic(): void
    {
        $on = $this->today();
        $sub = $this->subscriptions('testDowngradeToPaid');
        $this->assertSame(15, $sub->getRemainingDays($on));

        $basic = $this->subscriptionPlans('basic');
        $sub->setSubscriptionPlan($basic, $on);

        $this->assertSame($this->subscriptionPlans('basic'), $sub->getSubscriptionPlan());
        $this->assertSame($this->subscriptionPlans('premium'), $sub->getOriginalPlan());
        $remainingDays = $sub->getRemainingDays($on);
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
        $on = $this->today();
        $sub = $this->subscriptions('testRemainingAmountForMonthlyPlan');
        $this->assertSame(15, $sub->getRemainingDays($on));

        $premium = $this->subscriptionPlans('premium');
        $sub->setSubscriptionPlan($premium, $on);

        $this->assertSame($this->subscriptionPlans('premium'), $sub->getSubscriptionPlan());
        $this->assertSame($this->subscriptionPlans('basic'), $sub->getOriginalPlan());
        $remainingDays = $sub->getRemainingDays($on);
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
