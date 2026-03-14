<?php

declare(strict_types=1);

namespace Freemium\Domain;

final class RateCalculator
{
    /**
     * The amount to charge the gateway for one billing cycle.
     *
     * Coupon discount is applied to the cycle rate, not the normalized rate.
     * This ensures flat coupons deduct the correct amount regardless of plan period.
     */
    public function billingAmount(SubscriptionPlan $plan, ?Coupon $coupon = null): Money
    {
        $amount = $plan->getRate();

        if ($coupon !== null) {
            $amount = $coupon->getDiscount($amount);
        }

        return $amount;
    }

    /**
     * Monthly-normalized rate for a plan (no coupon applied).
     *
     * Used for rate comparisons, daily rate derivation, and credit calculations.
     */
    public function monthlyRate(SubscriptionPlan $plan): Money
    {
        $calculator = new PeriodCalculator($plan->getPeriod(), $plan->getFrequency());

        return $calculator->monthlyRate($plan->getRate());
    }

    public function yearlyRate(SubscriptionPlan $plan): Money
    {
        return $this->monthlyRate($plan)->multiply(12);
    }

    public function dailyRate(SubscriptionPlan $plan): Money
    {
        return $this->yearlyRate($plan)->divide('365', RoundingMode::HALF_UP);
    }
}
