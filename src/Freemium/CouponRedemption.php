<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

class CouponRedemption
{
    /**
     * Coupon used for this redemption.
     *
     * @var Coupon
     */
    private $coupon;

    /**
     * Subscription used for this redemption.
     *
     * @var Subscription
     */
    private $subscription;

    /**
     * When the coupon redeemed?
     *
     * @var DateTime
     */
    private $redeemedOn;

    /**
     * When redemption has been expired?.
     *
     * @var DateTime
     */
    private $expiredOn;

    public function __construct(Subscription $subscription, Coupon $coupon)
    {
        $this->coupon = $coupon;
        $this->subscription = $subscription;
        $this->redeemedOn = new DateTime('today');
    }

    /**
     * Expires current redemption.
     *
     * @return void
     */
    public function expire(): void
    {
        $this->expiredOn = new DateTime('today');
    }

    /**
     * Checks if redemption is active for the given date.
     * Default date is today.
     *
     * @param DateTime $date
     * @return bool
     */
    public function isActive(DateTime $date = null): bool
    {
        $date = $date ?: new DateTime('today');

        return $this->expiresOn() ? $date < $this->expiresOn() : true;
    }

    /**
     * Return future expiry date of redemption.
     *
     * @return DateTime|null
     */
    public function expiresOn(): ?DateTime
    {
        if ($months = $this->coupon->getDurationInMonths()) {
            $expiresOn = clone $this->getRedeemedOn();

            $expiresOn->modify("{$months} months");

            return $expiresOn;
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
     * @return DateTime|null
     */
    public function getExpiredOn(): ?DateTime
    {
        return $this->expiredOn;
    }

    /**
     * Get redeemed on date.
     *
     * @return DateTime
     */
    public function getRedeemedOn(): DateTime
    {
        return $this->redeemedOn;
    }
}
