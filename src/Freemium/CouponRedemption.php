<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class CouponRedemption extends \Larium\AbstractModel
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

    public function __construct()
    {
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

    public function auditCreate()
    {

    }
}
