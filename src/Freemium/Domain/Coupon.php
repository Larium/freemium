<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTimeImmutable;

class Coupon
{
    public const TOKEN_PREFIX = 'cpn_';

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
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $redemptionExpiration = null;

    /**
     * Months until this coupon stops working.
     * If the coupon is applied to a subscription this indicates the number of
     * months that the coupon will apply to subscription rate.
     *
     * @var int
     */
    private $durationInMonths;

    public function __construct(
        private readonly string $token,
        Discount $discount,
        ?string $redemptionKey = null
    ) {
        $this->discount = $discount;
        $this->redemptionKey = $redemptionKey ?? $this->generateCode();
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
     * @param int $redemptionCount Number of times this coupon has been redeemed (from repository)
     * @return bool
     */
    public function hasExpired(int $redemptionCount = 0): bool
    {
        return $this->redemptionExpiration && (new DateTimeImmutable('today')) > $this->redemptionExpiration
            || $this->redemptionLimit && $redemptionCount >= $this->redemptionLimit;
    }

    /**
     * Checks if Coupon can work with given plan.
     *
     * @param SubscriptionPlan $plan
     * @param SubscriptionPlan[] $applicablePlans Plans this coupon applies to; empty means all plans
     * @return bool
     */
    public function appliesToPlan(SubscriptionPlan $plan, array $applicablePlans = []): bool
    {
        if (empty($applicablePlans)) {
            return true;
        }

        foreach ($applicablePlans as $p) {
            if ($p === $plan || $p->getName() === $plan->getName()) {
                return true;
            }
        }

        return false;
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
