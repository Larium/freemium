<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTimeImmutable;

class SubscriptionChange
{
    /**
     * Previous subscription plan
     *
     * @var SubscriptionPlan
     */
    private $originalSubscriptionPlan;

    /**
     * Rate of previous subscription plan.
     *
     * @var Money
     */
    private Money $originalRate;

    /**
     * The new subscription plan.
     *
     * @var SubscriptionPlan
     */
    private $newSubscriptionPlan;

    /**
     * Rate of new subscription plan.
     *
     * @var Money
     */
    private Money $newRate;

    /**
     * Reason of subscription change.
     *
     * Available values are:
     * - REASON_NEW       (A subscription created)
     * - REASON_EXPIRE    (A subscription has expired)
     * - REASON_DOWNGRADE (A subscription was downgraded)
     * - REASON_UPGRADE   (A subscription was upagraded)
     * - REASON_CANCEL    (A subscription was cancelled)
     *
     * @var SubscriptionChangeReason The value for reason. @see SubscriptionChangeReason
     */
    private $reason;

    /**
     * When subscription change created?
     *
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $createdAt;

    public function __construct(
        Subscription $subscription,
        SubscriptionChangeReason $reason,
        SubscriptionPlan $originalPlan = null
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->reason = $reason;

        $this->newSubscriptionPlan = $subscription->getSubscriptionPlan();
        $this->newRate = $subscription->getSubscriptionPlan()->getRate();
        $this->originalSubscriptionPlan = $originalPlan;
        $currency = $subscription->getSubscriptionPlan()->getRate()->getCurrency();
        $this->originalRate = null === $originalPlan ? Money::zero($currency) : $originalPlan->getRate();
    }

    /**
     * Get change reason.
     *
     * @return SubscriptionChangeReason
     */
    public function getReason(): SubscriptionChangeReason
    {
        return $this->reason;
    }

    /**
     * Get original plan.
     *
     * @return SubscriptionPlan
     */
    public function getOriginalSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->originalSubscriptionPlan;
    }

    /**
     * Get new plan.
     *
     * @return SubscriptionPlan
     */
    public function getNewSubscriptionPlan(): SubscriptionPlan
    {
        return $this->newSubscriptionPlan;
    }

    /**
     * Get original plan rate.
     *
     * @return Money The rate of original plan in minor units
     */
    public function getOriginalRate(): Money
    {
        return $this->originalRate;
    }

    /**
     * Get new plan rate.
     *
     * @return Money The rate of new plan in minor units
     */
    public function getNewRate(): Money
    {
        return $this->newRate;
    }
}
