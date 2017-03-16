<?php

declare(strict_types=1);

namespace Freemium;

use DateTime;

class Transaction
{
    /**
     * Whether transaction was success or not.
     *
     * @var bool
     */
    private $success;

    /**
     * Credit card token used for this transaction.
     *
     * @var string
     */
    private $billing_key;

    /**
     * Amount paid for this transaction.
     *
     * @var int
     */
    private $amount;

    /**
     * Generic message that describes current transaction.
     *
     * @var string
     */
    private $message;

    /**
     * When transaction created?
     *
     * @var DateTime
     */
    private $created_at;

    /**
     * The subscription that created this transaction.
     *
     * @var Subscription
     */
    private $subscription;

    public function __construct(
        Subscription $subscription,
        int $amount,
        string $billing_key
    ) {
        $this->amount = $amount;
        $this->created_at = new DateTime();
        $this->billing_key = $billing_key;
        $this->subscription = $subscription;
    }

    /**
     * Set the received message from remote request.
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
    public function isSuccess()
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
