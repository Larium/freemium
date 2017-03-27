<?php

declare(strict_types=1);

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
     * @var Discount
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
    public function getDiscount(int $rate) : int
    {
        return $this->discount->apply($rate);
    }

    /**
     * Checks if Coupon has expired.
     *
     * @return bool
     */
    public function hasExpired() : bool
    {
        return $this->redemption_expiration && (new DateTime('today')) > $this->redemption_expiration
            || $this->redemption_limit && count($this->coupon_redemptions) >= $this->redemption_limit;
    }

    /**
     * Checks if Coupon can works with given plan.
     *
     * @param SubscriptionPlanInterface $plan
     * @return bool
     */
    public function appliesToPlan(SubscriptionPlanInterface $plan) : bool
    {
        if (empty($this->getSubscriptionPlans())) {
            return true; # applies to all plans
        }

        return $this->containsPlan($plan);
    }

    /**
     * Add a SubscriptionPlan to support this Coupon.
     *
     * @param SubscriptionPlanInterface
     * @return void
     */
    public function addSubscriptionPlan(SubscriptionPlanInterface $plan) : void
    {
        $this->subscription_plans[] = $plan;
    }

    public function clearSubscriptionPlans() : void
    {
        $this->subscription_plans = [];
    }

    public function getSubscriptionPlans() : array
    {
        return $this->subscription_plans;
    }

    public function getDurationInMonths() : ?int
    {
        return $this->duration_in_months;
    }

    private function generateCode() : string
    {
        $string = (string) mt_rand();

        return strtoupper(substr(base_convert(sha1(uniqid($string)), 16, 36), 0, 8));
    }

    private function containsPlan(SubscriptionPlanInterface $plan) : bool
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
