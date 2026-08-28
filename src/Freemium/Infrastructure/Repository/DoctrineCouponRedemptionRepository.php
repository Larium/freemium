<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Coupon;
use Freemium\Domain\CouponRedemption;
use Freemium\Domain\Repository\CouponRedemptionRepository;
use Freemium\Domain\Subscription;

final class DoctrineCouponRedemptionRepository implements CouponRedemptionRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function insert(CouponRedemption $redemption): void
    {
        $this->entityManager->persist($redemption);
        $this->entityManager->flush();
    }

    public function findBestActiveForSubscription(Subscription $subscription, DateTimeImmutable $date): ?CouponRedemption
    {
        $redemptions = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(CouponRedemption::class, 'r')
            ->where('r.subscription = :subscription')
            ->setParameter('subscription', $subscription)
            ->getQuery()
            ->getResult();

        $best = null;
        foreach ($redemptions as $redemption) {
            if (!$redemption->isActive($date)) {
                continue;
            }

            if ($best === null) {
                $best = $redemption;
                continue;
            }

            $bestDiscount = $best->getCoupon()->getDiscount($subscription->getRate());
            $candidateDiscount = $redemption->getCoupon()->getDiscount($subscription->getRate());
            if ($candidateDiscount->less($bestDiscount)) {
                $best = $redemption;
            }
        }

        return $best;
    }

    public function countByCoupon(Coupon $coupon): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(r.token)')
            ->from(CouponRedemption::class, 'r')
            ->where('r.coupon = :coupon')
            ->setParameter('coupon', $coupon)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
