<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class Transaction
{
    /**
     * Whether transaction was success or not.
     *
     * @var bool
     */
    protected $success;

    /**
     * Credit card token used for this transaction.
     *
     * @var string
     */
    protected $billing_key;

    /**
     * Amount paid for this transaction.
     *
     * @var int
     */
    protected $amount;

    /**
     * Generic message that describes current transaction.
     *
     * @var string
     */
    protected $message;

    /**
     * When transaction created?
     *
     * @var DateTime
     */
    protected $created_at;

    /**
     * The subscription that created this transaction.
     *
     * @var Freemium\Subscription
     */
    protected $subscription;

    public function __construct(
        Subscription $subscription,
        $amount,
        $billing_key
    ) {
        $this->amount = $amount;
        $this->created_at = new DateTime();
        $this->billing_key = $billing_key;
        $this->subscription = $subscription;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param $message string
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get success.
     *
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set success.
     *
     * @param $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * Get amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
