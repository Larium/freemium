<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class SubscriptionChange extends AbstractEntity
{
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
     * REason of subscription change.
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
