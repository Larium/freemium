<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Coupon;
use Freemium\Domain\SubscriptionPlan;

interface CouponPlanRepository
{
    /**
     * @return SubscriptionPlan[]
     */
    public function findPlansByCoupon(Coupon $coupon): array;

    public function attachPlanToCoupon(Coupon $coupon, SubscriptionPlan $plan): void;

    public function detachAllPlansFromCoupon(Coupon $coupon): void;
}
