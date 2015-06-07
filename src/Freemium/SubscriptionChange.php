<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class SubscriptionChange extends AbstractEntity
{
    /**
     * The model in your system that has the subscription.
     * Probably a User.
     *
     * @var SubscribableInterface
     * @access protected
     */
    protected $subscribable;

    /**
     * Previous subscription plan
     *
     * @var SubscriptionPlan
     * @access protected
     */
    protected $original_subscription_plan;

    /**
     * Rate of previous subscription plan in cents.
     *
     * @var integer
     * @access protected
     */
    protected $original_rate;

    /**
     * The new subscription plan.
     *
     * @var SubscriptionPlan
     * @access protected
     */
    protected $new_subscription_plan;

    /**
     * Rate of new subscription plan in cents
     *
     * @var integer
     * @access protected
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
     * @access protected
     */
    protected $reason;

    /**
     * When subscription change created?
     *
     * @var DateTime
     * @access protected
     */
    protected $created_at;

    /**
     * The subscription that changed plan.
     *
     * @var subscription
     * @access protected
     */
    protected $subscription;
}
