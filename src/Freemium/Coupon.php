<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class Coupon
{
    /**
     * Description.
     *
     * @var string
     */
    private $description;

    /**
     * The discount of coupon.
     *
     * @var Freemium\Discount
     */
    private $discount;

    /**
     * Unique code for this coupon.
     *
     * @var string
     */
    private $redemption_key;

    /**
     * How many times can be redeemed?
     *
     * @var int
     */
    private $redemption_limit;

    /**
     * The date until coupon is valid for redemption.
     *
     * @var DateTime
     */
    private $redemption_expiration;

    /**
     * Months until this coupon stops working.
     * If the coupon is applied to a subscription this indicates the number of
     * months that the coupon will apply to subscription rate.
     *
     * @var int
     */
    private $duration_in_months;

    private $coupon_redemptions = [];

    private $subscription_plans = [];

    public function __construct(Discount $discount, $redemptionKey = null)
    {
        $this->discount = $discount;
        if (null == $redemptionKey) {
            $this->redemption_key = $this->generateCode();
        }
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
     * @return bool
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
        if (empty($this->subscription_plans)) {
            return true; # applies to all plans
        }

        if (null === $plan) {
            return false;
        }

        return $this->containsPlan($plan);
    }

    public function addSubscriptionPlan(SubscriptionPlanInterface $plan)
    {
        $this->subscription_plans[] = $plan;
    }

    public function clearSubscriptionPlans()
    {
        $this->subscription_plans = [];
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

    private function containsPlan(SubscriptionPlanInterface $plan)
    {
        $exists = in_array($plan, $this->subscription_plans);
        $plans = array_filter(
            $this->subscription_plans,
            function ($p) use ($plan) {
                return $p->getName() === $plan->getName();
            }
        );

        return !empty($plans) || $exists;
    }
}
