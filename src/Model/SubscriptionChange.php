<?php

namespace Model;

class SubscriptionChange
{
    protected $id;

    protected $subscribable;

    protected $original_subscription_plan;

    protected $original_rate;

    protected $new_subscription_plan;

    protected $new_rate;

    protected $reason;

    protected $created_at;

    protected $subscription;


    public function __construct()
    {
        $this->created_at = new DateTime();
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
