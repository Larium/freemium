<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;

class SubscriptionChange
{
    /**
     * Previous subscription plan
     *
     * @var SubscriptionPlan
     */
    private $originalSubscriptionPlan;

    /**
     * Rate of previous subscription plan in cents.
     *
     * @var int
     */
    private $originalRate;

    /**
     * The new subscription plan.
     *
     * @var SubscriptionPlan
     */
    private $newSubscriptionPlan;

    /**
     * Rate of new subscription plan in cents
     *
     * @var int
     */
    private $newRate;

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
     * @var DateTime
     */
    private $createdAt;

    public function __construct(
        Subscription $subscription,
        SubscriptionChangeReason $reason,
        SubscriptionPlan $originalPlan = null
    ) {
        $this->createdAt = new DateTime();
        $this->reason = $reason;

        $this->newSubscriptionPlan = $subscription->getSubscriptionPlan();
        $this->newRate = $subscription->getSubscriptionPlan()->getRate();
        $this->originalSubscriptionPlan = $originalPlan;
        $this->originalRate = null == $originalPlan ? 0 : $originalPlan->getRate();
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
     * @return int The rate of original plan in cents.
     */
    public function getOriginalRate(): int
    {
        return $this->originalRate;
    }

    /**
     * Get new plan rate.
     *
     * @return int The rate of new plan in cents.
     */
    public function getNewRate(): int
    {
        return $this->newRate;
    }
}
