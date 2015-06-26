<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Doctrine\Common\Collections\ArrayCollection;
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

    protected $coupon_redemptions;

    protected $subscription_plans;

    public function __construct()
    {
        $this->coupon_redemptions = new ArrayCollection();
        $this->subscription_plans = new ArrayCollection();
    }

    /**
     * Applies coupon discount to given rate and returns it.
     *
     * @param float $rate
     * @access public
     * @return float
     */
    public function getDiscount($rate)
    {
        return round($rate * (1 - ($this->discount_percentage / 100)), 0);
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
            || $this->redemption_limit && $this->coupon_redemptions->count() >= $this->redemption_limit;
    }

    /**
     * Checks if Coupon can works with given plan.
     *
     * @param SubscriptionPlan $plan
     * @access public
     * @return boolean
     */
    public function appliesToPlan(SubscriptionPlan $plan)
    {
        if ($this->subscription_plans->isEmpty()) {
            return true; # applies to all plan
        }

        return $this->subscription_plans->contains($plan) ||
            !$this->subscription_plans->filter(function ($p) use ($plan) {
                return $p->getName() == $plan->getName();
            })->isEmpty();
    }
}
