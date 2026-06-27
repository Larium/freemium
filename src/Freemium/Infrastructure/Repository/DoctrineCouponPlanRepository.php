<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Coupon;
use Freemium\Domain\Repository\CouponPlanRepository;
use Freemium\Domain\SubscriptionPlan;

final class DoctrineCouponPlanRepository implements CouponPlanRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findPlansByCoupon(Coupon $coupon): array
    {
        $rows = $this->entityManager->getConnection()->fetchAllAssociative(
            'SELECT plan_token FROM coupon_plans WHERE coupon_token = :couponToken',
            ['couponToken' => $coupon->getToken()]
        );

        $plans = [];
        foreach ($rows as $row) {
            $plan = $this->entityManager->find(SubscriptionPlan::class, $row['plan_token']);
            if ($plan !== null) {
                $plans[] = $plan;
            }
        }

        return $plans;
    }

    public function attachPlanToCoupon(Coupon $coupon, SubscriptionPlan $plan): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'INSERT INTO coupon_plans (coupon_token, plan_token) VALUES (:couponToken, :planToken) ON CONFLICT DO NOTHING',
            [
                'couponToken' => $coupon->getToken(),
                'planToken' => $plan->getToken(),
            ]
        );
    }

    public function detachAllPlansFromCoupon(Coupon $coupon): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM coupon_plans WHERE coupon_token = :couponToken',
            ['couponToken' => $coupon->getToken()]
        );
    }
}
