<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Coupon;
use Freemium\Domain\SubscriptionPlan;

class CouponPlanStubRepository implements CouponPlanRepository
{
    /** @var array<string, SubscriptionPlan[]> coupon token => plans */
    private array $byCoupon = [];

    public function findPlansByCoupon(Coupon $coupon): array
    {
        return $this->byCoupon[$coupon->getToken()] ?? [];
    }

    public function attachPlanToCoupon(Coupon $coupon, SubscriptionPlan $plan): void
    {
        $token = $coupon->getToken();
        if (!isset($this->byCoupon[$token])) {
            $this->byCoupon[$token] = [];
        }
        if (!in_array($plan, $this->byCoupon[$token], true)) {
            $this->byCoupon[$token][] = $plan;
        }
    }

    public function detachAllPlansFromCoupon(Coupon $coupon): void
    {
        unset($this->byCoupon[$coupon->getToken()]);
    }
}
