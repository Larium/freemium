<?php

namespace Freemium\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CouponRedemptionTest extends TestCase
{
    use FixturesHelper;

    public function testExpiry()
    {
        $coupon = $this->coupons('fifteen_percent');

        $subscription = $this->createSubscription();

        $couponRedemption = new CouponRedemption($this->generateRedemptionToken(), $subscription, $coupon);

        $couponRedemption->expire();

        $this->assertEquals(new DateTimeImmutable('today'), $couponRedemption->getExpiredOn());
    }

    public function testActive()
    {
        $coupon = $this->coupons('one_month_duration');

        $subscription = $this->createSubscription();

        $couponRedemption = new CouponRedemption($this->generateRedemptionToken(), $subscription, $coupon);

        $this->assertTrue($couponRedemption->isActive());

        $fifteenDays = (new DateTimeImmutable())->modify('+15 days');
        $this->assertTrue($couponRedemption->isActive($fifteenDays));

        $oneMonth = (new DateTimeImmutable('today'))->modify('+1 month');
        $this->assertFalse($couponRedemption->isActive($oneMonth));

        $this->assertEquals($oneMonth, $couponRedemption->expiresOn());
    }

    public function testApplyCoupon()
    {
        $redemptionRepo = new \Freemium\Domain\Repository\CouponRedemptionStubRepository();
        $planRepo = new \Freemium\Domain\Repository\CouponPlanStubRepository();
        $handler = new \Freemium\Application\UseCase\ApplyCoupon\ApplyCouponHandler(
            new \Freemium\Application\Event\EventProvider(),
            $redemptionRepo,
            $planRepo
        );

        $sub = $this->subscriptions('testApplyCoupon');
        $coupon = $this->coupons('sample');
        $original_price = $sub->getSubscriptionPlan()->getRate();

        $handler->handle(new \Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon(
            $sub,
            $coupon,
            $this->generateRedemptionToken()
        ));

        $redemption = $redemptionRepo->findBestActiveForSubscription($sub, new \DateTimeImmutable('today'));
        $this->assertNotNull($redemption);
        $this->assertNotNull($redemption->getSubscription());
        // After applying coupon, billing amount equals discount applied to plan cycle rate.
        $this->assertTrue($sub->billingAmount($coupon)->equals($coupon->getDiscount($original_price)));
        // No plan change so remaining amount (for conversion) is zero.
        $this->assertTrue($sub->remainingAmount($this->today())->equals(Money::zero($sub->getRate()->getCurrency())));
    }

    public function testApplyCouponForSpecificPlan()
    {
        $redemptionRepo = new \Freemium\Domain\Repository\CouponRedemptionStubRepository();
        $planRepo = new \Freemium\Domain\Repository\CouponPlanStubRepository();
        $handler = new \Freemium\Application\UseCase\ApplyCoupon\ApplyCouponHandler(
            new \Freemium\Application\Event\EventProvider(),
            $redemptionRepo,
            $planRepo
        );

        $sub = $this->subscriptions('testApplyCoupon');
        $coupon = $this->coupons('sample');
        $planRepo->attachPlanToCoupon($coupon, $this->subscriptionPlans('basic'));

        $handler->handle(new \Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon(
            $sub,
            $coupon,
            $this->generateRedemptionToken()
        ));

        $planRepo->detachAllPlansFromCoupon($coupon);
        $planRepo->attachPlanToCoupon($coupon, $this->subscriptionPlans('premium'));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Coupon does not apply');
        $handler->handle(new \Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon(
            $sub,
            $coupon,
            $this->generateRedemptionToken()
        ));
    }

    private function createSubscription()
    {
        return new Subscription(
            $this->generateSubscriptionToken(),
            $this->users('bob'),
            $this->subscriptionPlans('free'),
            $this->today()
        );
    }
}
