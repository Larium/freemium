<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use DateTimeImmutable;
use Freemium\Domain\Coupon;
use Freemium\Domain\CouponRedemption;
use Freemium\Domain\Subscription;

use function bccomp;

class CouponRedemptionStubRepository implements CouponRedemptionRepository
{
    /** @var CouponRedemption[] */
    private array $redemptions = [];

    public function insert(CouponRedemption $redemption): void
    {
        $this->redemptions[] = $redemption;
    }

    public function findBestActiveForSubscription(Subscription $subscription, DateTimeImmutable $date): ?CouponRedemption
    {
        $forSubscription = array_filter($this->redemptions, function (CouponRedemption $r) use ($subscription) {
            return $r->getSubscription() === $subscription;
        });

        $active = array_filter($forSubscription, function (CouponRedemption $r) use ($date) {
            return $r->isActive($date);
        });

        if (empty($active)) {
            return null;
        }

        $rate = $subscription->getSubscriptionPlan()->getRate();
        usort($active, function (CouponRedemption $a, CouponRedemption $b) use ($rate) {
            $aDiscount = $a->getCoupon()->getDiscount($rate);
            $bDiscount = $b->getCoupon()->getDiscount($rate);

            return (int) bccomp($aDiscount->getMinorAmount(), $bDiscount->getMinorAmount());
        });

        return reset($active) ?: null;
    }

    public function countByCoupon(Coupon $coupon): int
    {
        return count(array_filter($this->redemptions, function (CouponRedemption $r) use ($coupon) {
            return $r->getCoupon() === $coupon;
        }));
    }
}
