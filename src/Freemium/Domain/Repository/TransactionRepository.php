<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Transaction;

interface TransactionRepository
{
    public function findByIdempotencyKey(string $idempotencyKey): ?Transaction;

    public function insert(Transaction $transaction): void;

    public function update(Transaction $transaction): void;
}
