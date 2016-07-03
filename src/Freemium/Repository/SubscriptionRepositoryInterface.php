<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Repository;

interface SubscriptionRepositoryInterface
{
    /**
     * Return all subscriptions that must receive payment, meanining that
     * paidThrough <= today and that their rate is > 0
     *
     * @return array|Traversable
     */
    public function findBillable();

    /**
     * Return all expired subscriptions.
     *
     * @return array|Traversable
     */
    public function findExpired();
}
