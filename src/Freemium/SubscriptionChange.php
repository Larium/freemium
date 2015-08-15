<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class SubscriptionChange extends \Larium\AbstractModel
{
    const REASON_NEW        = 1;

    const REASON_EXPIRE     = 2;

    const REASON_UPGRADE    = 3;

    const REASON_DOWNGRADE  = 4;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var SubscribableInterface
     */
    protected $subscribable;

    /**
     * Previous subscription plan
     *
     * @var SubscriptionPlan
     */
    protected $original_subscription_plan;

    /**
     * Rate of previous subscription plan in cents.
     *
     * @var integer
     */
    protected $original_rate;

    /**
     * The new subscription plan.
     *
     * @var SubscriptionPlan
     */
    protected $new_subscription_plan;

    /**
     * Rate of new subscription plan in cents
     *
     * @var integer
     */
    protected $new_rate;

    /**
     * Reason of subscription change.
     *
     * Available values are:
     * - new         (A subscription created)
     * - expiration  (A subscription has expired)
     * - downgrade   (A subscription was downgraded)
     * - upgrade     (A subscription was upagraded)
     * - cancelation (A subscription was cancelled)
     *
     * @var string
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
     * @var subscription
     */
    protected $subscription;
}
