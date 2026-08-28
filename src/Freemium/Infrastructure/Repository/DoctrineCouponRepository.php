<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Coupon;

final class DoctrineCouponRepository implements CouponRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->entityManager->getRepository(Coupon::class)->findOneBy(['redemptionKey' => $code]);
    }
}
