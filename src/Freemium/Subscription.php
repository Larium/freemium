<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class Subscription
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
     * Which service plan this subscription is for.
     * Affects how payment is interpreted.
     *
     * @var SubscriptionPlan
     * @access protected
     */
    protected $subscription_plan;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     *
     * @var DateTime
     * @access protected
     */
    protected $paid_through;

    /**
     * The id for this user in the remote billing gateway.
     * May not exist if user is on a free plan.
     *
     * @var string
     * @access protected
     */
    protected $billing_key;

    /**
     * When the last gateway transaction was for this account.
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTime
     * @access protected
     */
    protected $last_transaction_at;

    /**
     * @var array<CouponRedemptions>
     * @access protected
     */
    protected $coupon_redemptions;
}
