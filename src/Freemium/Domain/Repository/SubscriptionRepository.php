<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Subscription;
use Freemium\Domain\Transaction;

interface SubscriptionRepository
{
    public function insert(Subscription $subscription): void;

    public function update(Subscription $subscription): void;

    public function remove(Subscription $subscription): void;

    public function findByToken(string $token): ?Subscription;

    public function findTransactionByIdempotencyKey(Subscription $subscription, string $idempotencyKey): ?Transaction;

    /**
     * Return all subscriptions that must receive payment, meaning that
     * paidThrough <= today and that their rate is greater than zero
     * (e.g. getRate()->greater(Money::zero($subscription->getRate()->getCurrency()))).
     *
     * @return iterable
     */
    public function findBillable(): iterable;

    /**
     * Return all expired subscriptions.
     *
     * @return iterable
     */
    public function findExpired(): iterable;
}
