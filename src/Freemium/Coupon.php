<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class Coupon extends AbstractEntity
{
    /**
     * Description.
     *
     * @var string
     * @access protected
     */
    protected $description;

    /**
     * Percentage discount.
     *
     * @var integer
     * @access protected
     */
    protected $discount_percentage;

    /**
     * Flat discount, in cents
     *
     * @var integer
     * @access protected
     */
    protected $discount_flat;

    /**
     * Unique code for this coupon.
     *
     * @var string
     * @access protected
     */
    protected $redemption_key;

    /**
     * How many times can be redeemed?
     *
     * @var integer
     * @access protected
     */
    protected $redemption_limit;

    /**
     * The date until coupon is valid for redemption.
     *
     * @var DateTime
     * @access protected
     */
    protected $redemption_expiration;

    /**
     * duration_in_months
     *
     * @var integer
     * @access protected
     */
    protected $duration_in_months;

    protected $coupon_redemptions = array();

    public function hasExpired()
    {
        return $this->redemption_expiration && (new DateTime('today')) > $this->redemption_expiration
            || $this->redemption_limit && count($this->coupon_redemptions) >= $this->redemption_limit;
    }
}
