<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Transaction;

class TransactionStubRepository implements TransactionRepository
{
    /** @var array<string, Transaction> idempotencyKey => Transaction */
    private array $byIdempotencyKey = [];

    public function findByIdempotencyKey(string $idempotencyKey): ?Transaction
    {
        return $this->byIdempotencyKey[$idempotencyKey] ?? null;
    }

    public function insert(Transaction $transaction): void
    {
        $key = $transaction->getIdempotencyKey();
        if ($key !== null) {
            $this->byIdempotencyKey[$key] = $transaction;
        }
    }

    public function update(Transaction $transaction): void
    {
        // No-op for stub; in-memory transaction already has captured state
    }
}
