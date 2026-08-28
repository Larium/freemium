<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Freemium\Domain\Coupon;

interface CouponRepository
{
    public function findByCode(string $code): ?Coupon;
}
