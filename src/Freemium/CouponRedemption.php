<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Carbon\Carbon;
use DateTime;

class CouponRedemption extends AbstractEntity
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
     * @access protected
     */
    protected $subscription;

    /**
     * When the coupon redeemed?
     *
     * @var DateTime
     * @access protected
     */
    protected $redeemed_on;

    /**
     * Until when the redemption is valid?
     *
     * @var DateTime
     * @access protected
     */
    protected $expired_on;

    public function __construct(Coupon $coupon, Subscription $subscription)
    {
        $this->coupon = $coupon;
        $this->subscription = $subscription;

        $this->redeemed_on = new DateTime('today');
    }

    public function expire()
    {
        $this->expired_on = new DateTime('today');
    }

    public function isActive($date = null)
    {
        $date = $date ?: new DateTime('today');

        return $this->expiresOn() ? $date <= $this->expiresOn() : true;
    }

    /**
     * Return expires on date.
     *
     * @access public
     * @return DateTime|null
     */
    public function expiresOn()
    {
        if ($months = $this->coupon->getDurationInMonths()) {
            $redeemed_on = clone $this->getRedeemedOn();

            $redeemed_on->modify("{$months} months");

            return $redeemed_on;
        }
    }

    /**
     *
     * @access public
     * @return DateTime
     */
    public function getRedeemedOn()
    {
        return $this->redeemed_on ?: new DateTime('today');
    }
}
