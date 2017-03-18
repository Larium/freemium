<?php

declare(strict_types=1);

namespace Freemium\Repository;

class SubscriptionStubRepository implements SubscriptionRepositoryInterface
{
    public function findBillable() : iterable
    {
    }

    public function findExpired() : iterable
    {
    }

    public function find($id)
    {
    }

    public function insert($entity)
    {
    }

    public function update($entity)
    {
    }

    public function remove($entity)
    {
    }
}
