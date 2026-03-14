<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTime;
use AktiveMerchant\Billing\Response;

class Transaction
{
    public const TOKEN_PREFIX = 'txn_';

    private readonly string $token;

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

    private readonly ?string $idempotencyKey;

    public function __construct(string $token, Money $amount, ?string $idempotencyKey = null)
    {
        $this->token = $token;
        $this->amount = $amount;
        $this->idempotencyKey = $idempotencyKey;
        $this->createdAt = new DateTime();
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function getToken(): string
    {
        return $this->token;
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
