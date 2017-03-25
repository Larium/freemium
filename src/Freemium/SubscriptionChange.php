<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

class SubscriptionChange
{
    /**
     * Previous subscription plan
     *
     * @var SubscriptionPlan
     */
    private $original_subscription_plan;

    /**
     * Rate of previous subscription plan in cents.
     *
     * @var int
     */
    private $original_rate;

    /**
     * The new subscription plan.
     *
     * @var SubscriptionPlan
     */
    private $new_subscription_plan;

    /**
     * Rate of new subscription plan in cents
     *
     * @var int
     */
    private $new_rate;

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
     * @var int The value for reason. @see Freemium\SubscriptionChangeInterface
     */
    private $reason;

    /**
     * When subscription change created?
     *
     * @var DateTime
     */
    private $created_at;

    /**
     * The subscription that changed plan.
     *
     * @var Freemium\Subscription
     */
    private $subscription;

    public function __construct(
        Subscription $subscription,
        int $reason,
        SubscriptionPlanInterface $original_plan = null
    ) {
        $this->created_at = new DateTime();
        $this->subscription = $subscription;
        $this->reason = $reason;

        $this->new_subscription_plan = $subscription->getSubscriptionPlan();
        $this->new_rate = $subscription->getSubscriptionPlan()->getRate();
        $this->original_subscription_plan = $original_plan;
        $this->original_rate = null == $original_plan ? 0 : $original_plan->getRate();
    }

    /**
     * Get change reason.
     *
     * @return int
     */
    public function getReason() : int
    {
        return $this->reason;
    }

    /**
     * Get original plan.
     *
     * @return SubscriptionPlanInterface
     */
    public function getOriginalSubscriptionPlan() : ?SubscriptionPlanInterface
    {
        return $this->original_subscription_plan;
    }

    /**
     * Get new plan.
     *
     * @return SubscriptionPlanInterface
     */
    public function getNewSubscriptionPlan() : SubscriptionPlanInterface
    {
        return $this->new_subscription_plan;
    }

    /**
     * Get original plan rate.
     *
     * @return int The rate of original plan in cents.
     */
    public function getOriginalRate() : int
    {
        return $this->original_rate;
    }

    /**
     * Get new plan rate.
     *
     * @return int The rate of new plan in cents.
     */
    public function getNewRate() : int
    {
        return $this->new_rate;
    }
}
