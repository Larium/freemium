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
     * Months until this coupon stops working.
     *
     * @var integer
     * @access protected
     */
    protected $duration_in_months;

    protected $coupon_redemptions = array();

    protected $subscription_plans = array();

    /**
     * Applies coupon discount to given rate and returns it.
     *
     * @param float $rate
     * @access public
     * @return float
     */
    public function getDiscount($rate)
    {
        return $rate * ((1 - (float)$this->discount_percentage) / 100);
    }

    /**
     * Checks if Coupon has expired.
     *
     * @access public
     * @return booleam
     */
    public function hasExpired()
    {
        return $this->redemption_expiration && (new DateTime('today')) > $this->redemption_expiration
            || $this->redemption_limit && count($this->coupon_redemptions) >= $this->redemption_limit;
    }

    public function appliesToPlan(SubscriptionPlan $plan)
    {
        if (empty($this->subscription_plans)) {
            return true; # applies to all plan
        }

        return in_array($plan, $this->subscription_plans, true);
    }
}
