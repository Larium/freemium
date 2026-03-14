<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Subscription;
use Freemium\Domain\Transaction;

class SubscriptionStubRepository implements SubscriptionRepository
{
    public function findByToken(string $token): ?Subscription
    {
        return null;
    }

    public function findBillable(): iterable
    {
        return [];
    }

    public function findExpired(): iterable
    {
        return [];
    }

    public function findTransactionByIdempotencyKey(Subscription $subscription, string $idempotencyKey): ?Transaction
    {
        foreach ($subscription->getTransactions() as $transaction) {
            if ($transaction->getIdempotencyKey() === $idempotencyKey) {
                return $transaction;
            }
        }

        return null;
    }

    public function find($id)
    {
    }

    public function insert($entity): void
    {
    }

    public function update($entity): void
    {
    }

    public function remove($entity): void
    {
    }
}
