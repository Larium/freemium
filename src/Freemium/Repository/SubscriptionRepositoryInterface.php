<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Repository;

interface SubscriptionRepositoryInterface
{
    /**
     * Return all subscriptions that must receive payment, meanining that
     * paidThrough <= today and that their rate is > 0
     *
     * @access public
     * @return array
     */
    public function findBillable();

    /**
     * Return all expired subscriptions.
     *
     * @access public
     * @return array
     */
    public function findExpired();
}
