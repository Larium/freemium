<?php

declare(strict_types = 1);

namespace Freemium;

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
     * Id reference of a subscription in remote gateway.
     *
     * @var string
     */
    private $transactionId;

    public function __construct(
        Response $response,
        int $amount
    ) {
        $this->amount = $amount;
        $this->created_at = new DateTime();
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
