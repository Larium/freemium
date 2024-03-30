<?php

declare(strict_types=1);

namespace Freemium\Repository;

use Freemium\Subscription;

interface SubscriptionRepository
{
    public function insert(Subscription $subscription): void;

    public function update(Subscription $subscription): void;

    public function remove(Subscription $subscription): void;

    /**
     * Return all subscriptions that must receive payment, meanining that
     * paidThrough <= today and that their rate is > 0
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
