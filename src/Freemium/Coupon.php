<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Coupon
{
    /**
     * Description.
     *
     * @var string
     */
    protected $description;

    /**
     * The discount of coupon.
     *
     * @var Discount
     */
    protected $discount;

    /**
     * Unique code for this coupon.
     *
     * @var string
     */
    protected $redemption_key;

    /**
     * How many times can be redeemed?
     *
     * @var int
     */
    protected $redemption_limit;

    /**
     * The date until coupon is valid for redemption.
     *
     * @var DateTime
     */
    protected $redemption_expiration;

    /**
     * Months until this coupon stops working.
     * If the coupon is applied to a subscription this indicates the number of
     * months that the coupon will apply to subscription rate.
     *
     * @var int
     */
    protected $duration_in_months;

    protected $coupon_redemptions;

    protected $subscription_plans;

    public function __construct(Discount $discount, $redemptionKey = null)
    {
        $this->discount = $discount;
        if (null == $redemptionKey) {
            $this->redemption_key = $this->generateCode();
        }
        $this->coupon_redemptions = new ArrayCollection();
        $this->subscription_plans = new ArrayCollection();
    }

    /**
     * Returns dicounted price for the given rate.
     * @see Discount::getDiscountRate
     *
     * @param int $rate
     * @return int
     */
    public function getDiscount($rate)
    {
        return $this->discount->calculate($rate);
    }

    /**
     * Checks if Coupon has expired.
     *
     * @return boolean
     */
    public function hasExpired()
    {
        return $this->redemption_expiration && (new DateTime('today')) > $this->redemption_expiration
            || $this->redemption_limit && $this->coupon_redemptions->count() >= $this->redemption_limit;
    }

    /**
     * Checks if Coupon can works with given plan.
     *
     * @param SubscriptionPlanInterface $plan
     * @return boolean
     */
    public function appliesToPlan(SubscriptionPlanInterface $plan = null)
    {
        if ($this->subscription_plans->isEmpty()) {
            return true; # applies to all plans
        }

        if (null === $plan) {
            return false;
        }

        return $this->subscription_plans->contains($plan) ||
            !$this->subscription_plans->filter(function ($p) use ($plan) {
                return $p->getName() == $plan->getName();
            })->isEmpty();
    }

    public function addSubscriptionPlan(SubscriptionPlanInterface $plan)
    {
        $this->subscription_plans[] = $plan;
    }

    public function getSubscriptionPlans()
    {
        return $this->subscription_plans;
    }

    public function getDurationInMonths()
    {
        return $this->duration_in_months;
    }

    private function generateCode()
    {
        return strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8));
    }
}
