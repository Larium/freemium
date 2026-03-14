<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;
use AktiveMerchant\Billing\Response;

class Transaction
{
    /**
     * Whether transaction was success or not.
     *
     * @var bool
     */
    private $success;

    /**
     * Amount paid for this transaction.
     *
     * @var Money
     */
    private Money $amount;

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
    private $createdAt;

    /**
     * Id reference of a subscription in remote gateway.
     *
     * @var string
     */
    private $transactionId;

    public function __construct(Money $amount)
    {
        $this->amount = $amount;
        $this->createdAt = new DateTime();
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function capture(Response $response): void
    {
        $this->success = $response->success();
        $this->message = $response->message();
        $this->transactionId = $response->authorization();
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
}
