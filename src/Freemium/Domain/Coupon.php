<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;

class Coupon
{
    public const TOKEN_PREFIX = 'cpn_';

    private readonly string $token;

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
    private $redemptionKey;

    /**
     * How many times can be redeemed?
     *
     * @var int
     */
    private $redemptionLimit;

    /**
     * The date until coupon is valid for redemption.
     *
     * @var DateTime
     */
    private $redemptionExpiration;

    /**
     * Months until this coupon stops working.
     * If the coupon is applied to a subscription this indicates the number of
     * months that the coupon will apply to subscription rate.
     *
     * @var int
     */
    private $durationInMonths;

    private $couponRedemptions = [];

    private $subscriptionPlans = [];

    public function __construct(string $token, Discount $discount, $redemptionKey = null)
    {
        $this->token = $token;
        $this->discount = $discount;
        if (null == $redemptionKey) {
            $this->redemptionKey = $this->generateCode();
        } else {
            $this->redemptionKey = $redemptionKey;
        }
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns discounted price for the given rate.
     * @see Discount::apply
     *
     * @param Money $rate Rate in minor units
     * @return Money Discounted amount in same currency
     */
    public function getDiscount(Money $rate): Money
    {
        return $this->discount->apply($rate);
    }

    /**
     * Checks if Coupon has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        return $this->redemptionExpiration && (new DateTime('today')) > $this->redemptionExpiration
            || $this->redemptionLimit && count($this->couponRedemptions) >= $this->redemptionLimit;
    }

    /**
     * Checks if Coupon can works with given plan.
     *
     * @param SubscriptionPlan $plan
     * @return bool
     */
    public function appliesToPlan(SubscriptionPlan $plan): bool
    {
        if (empty($this->getSubscriptionPlans())) {
            return true; # applies to all plans
        }

        return $this->containsPlan($plan);
    }

    /**
     * Add a SubscriptionPlan to support this Coupon.
     *
     * @param SubscriptionPlan
     * @return void
     */
    public function addSubscriptionPlan(SubscriptionPlan $plan): void
    {
        $this->subscriptionPlans[] = $plan;
    }

    public function clearSubscriptionPlans(): void
    {
        $this->subscriptionPlans = [];
    }

    public function getSubscriptionPlans(): array
    {
        return $this->subscriptionPlans;
    }

    public function getDurationInMonths(): ?int
    {
        return $this->durationInMonths;
    }

    private function generateCode(): string
    {
        $string = (string) \random_int(0, \PHP_INT_MAX);

        return strtoupper(substr(base_convert(sha1(uniqid($string)), 16, 36), 0, 8));
    }

    private function containsPlan(SubscriptionPlan $plan): bool
    {
        $exists = in_array($plan, $this->subscriptionPlans);
        $plans = array_filter(
            $this->subscriptionPlans,
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
