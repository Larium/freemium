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
     * When redemption has been expired?.
     *
     * @var DateTime
     * @access protected
     */
    protected $expired_on;

    public function __construct()
    {
        $this->redeemed_on = new DateTime('today');
    }

    /**
     * Expires current redemption.
     *
     * @access public
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
     * @access public
     * @return boolean
     */
    public function isActive(DateTime $date = null)
    {
        $date = $date ?: new DateTime('today');

        return $this->expiresOn() ? $date <= $this->expiresOn() : true;
    }

    /**
     * Return future expiry date of redemption.
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

    public function auditCreate()
    {

    }
}
