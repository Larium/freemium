<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ApplyCoupon;

use Freemium\Domain\Coupon;
use Freemium\Domain\Subscription;

class ApplyCoupon
{
    public function __construct(
        private readonly Subscription $subscription,
        private readonly Coupon $coupon,
        private readonly string $redemptionToken
    ) {
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    public function getRedemptionToken(): string
    {
        return $this->redemptionToken;
    }
}
