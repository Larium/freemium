<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use DateTimeImmutable;
use Freemium\Domain\Coupon;
use Freemium\Domain\CouponRedemption;
use Freemium\Domain\Subscription;

interface CouponRedemptionRepository
{
    public function insert(CouponRedemption $redemption): void;

    public function findBestActiveForSubscription(Subscription $subscription, DateTimeImmutable $date): ?CouponRedemption;

    public function countByCoupon(Coupon $coupon): int;
}
