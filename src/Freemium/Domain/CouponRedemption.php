<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTimeImmutable;

class CouponRedemption
{
    public const TOKEN_PREFIX = 'red_';

    private readonly string $token;

    /**
     * Coupon used for this redemption.
     *
     * @var Coupon
     */
    private Coupon $coupon;

    /**
     * Subscription used for this redemption.
     *
     * @var Subscription
     */
    private Subscription $subscription;

    /**
     * When the coupon redeemed?
     *
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $redeemedOn;

    /**
     * When redemption has been expired?.
     *
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $expiredOn = null;

    public function __construct(string $token, Subscription $subscription, Coupon $coupon)
    {
        $this->token = $token;
        $this->coupon = $coupon;
        $this->subscription = $subscription;
        $this->redeemedOn = new DateTimeImmutable('today');
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Expires current redemption.
     *
     * @return void
     */
    public function expire(): void
    {
        $this->expiredOn = new DateTimeImmutable('today');
    }

    /**
     * Checks if redemption is active for the given date.
     * Default date is today.
     *
     * @param DateTimeImmutable|null $date
     * @return bool
     */
    public function isActive(?DateTimeImmutable $date = null): bool
    {
        $date = $date ?? new DateTimeImmutable('today');

        return $this->expiresOn() ? $date < $this->expiresOn() : true;
    }

    /**
     * Return future expiry date of redemption.
     *
     * @return DateTimeImmutable|null
     */
    public function expiresOn(): ?DateTimeImmutable
    {
        if ($months = $this->coupon->getDurationInMonths()) {
            return $this->getRedeemedOn()->modify("{$months} months");
        }

        return null;
    }

    /**
     * Get coupon.
     *
     * @return Coupon
     */
    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    /**
     * Get subscription.
     *
     * @return Subscription
     */
    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    /**
     * Get expired on date.
     *
     * @return DateTimeImmutable|null
     */
    public function getExpiredOn(): ?DateTimeImmutable
    {
        return $this->expiredOn;
    }

    /**
     * Get redeemed on date.
     *
     * @return DateTimeImmutable
     */
    public function getRedeemedOn(): DateTimeImmutable
    {
        return $this->redeemedOn;
    }
}
