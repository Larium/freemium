<?php

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
    private $redeemed_on;

    /**
     * When redemption has been expired?.
     *
     * @var DateTime
     */
    private $expired_on;

    public function __construct($subscription, $coupon)
    {
        $this->coupon = $coupon;
        $this->subscription = $subscription;
        $this->redeemed_on = new DateTime('today');
    }

    /**
     * Expires current redemption.
     *
     * @return void
     */
    public function expire()
    {
        $this->expired_on = new DateTime('today');
    }

    /**
     * Checks if redemption is active for the given date.
     * Default date is today.
     *
     * @param DateTime $date
     * @return bool
     */
    public function isActive(DateTime $date = null)
    {
        $date = $date ?: new DateTime('today');

        return $this->expiresOn() ? $date < $this->expiresOn() : true;
    }

    /**
     * Return future expiry date of redemption.
     *
     * @return DateTime|null
     */
    public function expiresOn()
    {
        if ($months = $this->coupon->getDurationInMonths()) {
            $expires_on = clone $this->getRedeemedOn();

            $expires_on->modify("{$months} months");

            return $expires_on;
        }
    }

    /**
     * Get coupon.
     *
     * @return Freemium\Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * Get subscription.
     *
     * @return Freemium\Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Get expired on date.
     *
     * @return DateTime
     */
    public function getExpiredOn()
    {
        return $this->expired_on;
    }

    /**
     * Get redeemed on date.
     *
     * @return DateTime
     */
    public function getRedeemedOn()
    {
        return $this->redeemed_on;
    }
}
