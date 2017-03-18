<?php

declare(strict_types=1);

namespace Freemium\Repository;

interface SubscriptionRepositoryInterface extends RepositoryInterface
{
    /**
     * Return all subscriptions that must receive payment, meanining that
     * paidThrough <= today and that their rate is > 0
     *
     * @return iterable
     */
    public function findBillable() : iterable;

    /**
     * Return all expired subscriptions.
     *
     * @return iterable
     */
    public function findExpired() : iterable;
}
