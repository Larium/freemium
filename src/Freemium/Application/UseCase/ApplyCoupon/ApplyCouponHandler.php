<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ApplyCoupon;

use DomainException;
use Freemium\Domain\CouponRedemption;
use Freemium\Application\UseCase\AbstractCommandHandler;
use Freemium\Domain\Repository\CouponPlanRepository;
use Freemium\Domain\Repository\CouponRedemptionRepository;

class ApplyCouponHandler extends AbstractCommandHandler
{
    public function __construct(
        \Freemium\Application\Event\EventProvider $eventProvider,
        private readonly CouponRedemptionRepository $couponRedemptionRepository,
        private readonly CouponPlanRepository $couponPlanRepository
    ) {
        parent::__construct($eventProvider);
    }

    public function handle(ApplyCoupon $command): void
    {
        $subscription = $command->getSubscription();
        $coupon = $command->getCoupon();
        $applicablePlans = $this->couponPlanRepository->findPlansByCoupon($coupon);

        if (!$coupon->appliesToPlan($subscription->getSubscriptionPlan(), $applicablePlans)) {
            throw new DomainException('Coupon does not apply to this subscription plan.');
        }

        $redemption = new CouponRedemption(
            $command->getRedemptionToken(),
            $subscription,
            $coupon
        );

        $this->couponRedemptionRepository->insert($redemption);
    }
}
