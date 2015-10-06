<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

trait CouponRedemption
{
    /**
     * Coupon used for this redemption.
     *
     * @var Coupon
     * @access protected
     */
    protected $coupon;

    /**
     * Subscription used for this redemption.
     *
     * @var Subscription
     */
    protected $subscription;

    /**
     * When the coupon redeemed?
     *
     * @var DateTime
     */
    protected $redeemed_on;

    /**
     * When redemption has been expired?.
     *
     * @var DateTime
     */
    protected $expired_on;

    public function __construct($subscription, $coupon)
    {
        $this->subscription = $subscription;
        $this->coupon       = $coupon;
        $this->redeemed_on  = new DateTime('today');
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
     * @return boolean
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
     * @return coupon.
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * Get subscription.
     *
     * @return subscription.
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Get expired_on.
     *
     * @return expired_on.
     */
    public function getExpiredOn()
    {
        return $this->expired_on;
    }

    /**
     * Get redeemed_on.
     *
     * @return redeemed_on.
     */
    public function getRedeemedOn()
    {
        return $this->redeemed_on;
    }
}
