<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class SubscriptionChange
{
    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var Freemium\SubscribableInterface
     */
    protected $subscribable;

    /**
     * Previous subscription plan
     *
     * @var Freemium\SubscriptionPlan
     */
    protected $original_subscription_plan;

    /**
     * Rate of previous subscription plan in cents.
     *
     * @var int
     */
    protected $original_rate;

    /**
     * The new subscription plan.
     *
     * @var Freemium\SubscriptionPlan
     */
    protected $new_subscription_plan;

    /**
     * Rate of new subscription plan in cents
     *
     * @var int
     */
    protected $new_rate;

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
    protected $reason;

    /**
     * When subscription change created?
     *
     * @var DateTime
     */
    protected $created_at;

    /**
     * The subscription that changed plan.
     *
     * @var Freemium\Subscription
     */
    protected $subscription;

    public function __construct(
        $subscription,
        $reason,
        SubscriptionPlanInterface $original_plan = null
    ) {
        $this->created_at = new DateTime();
        $this->subscription = $subscription;
        $this->subscribable = $subscription->getSubscribable();
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
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Get original plan.
     *
     * @return Freemium\SubscriptionPlanInterface
     */
    public function getOriginalSubscriptionPlan()
    {
        return $this->original_subscription_plan;
    }

    /**
     * Get new plan.
     *
     * @return Freemium\SubscriptionPlanInterface
     */
    public function getNewSubscriptionPlan()
    {
        return $this->new_subscription_plan;
    }

    /**
     * Get original plan rate.
     *
     * @return int The rate of original plan in cents.
     */
    public function getOriginalRate()
    {
        return $this->original_rate;
    }

    /**
     * Get new plan rate.
     *
     * @return int The rate of new plan in cents.
     */
    public function getNewRate()
    {
        return $this->new_rate;
    }
}
