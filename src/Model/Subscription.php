<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

use Doctrine\Common\Collections\ArrayCollection;

class Subscription
{
    protected $id;

    protected $subscribable;

    protected $subscription_plan;

    protected $original_plan;

    protected $paid_through;

    protected $started_on;

    protected $billing_key;

    protected $last_transaction_at;

    protected $coupon_redemptions;

    protected $in_trial = false;

    protected $credit_card;

    protected $credit_card_changed;

    protected $subscription_changes;

    protected $expire_on;

    protected $transactions;

    protected $rate;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->coupon_redemptions = new ArrayCollection();
        $this->subscription_changes = new ArrayCollection();
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
