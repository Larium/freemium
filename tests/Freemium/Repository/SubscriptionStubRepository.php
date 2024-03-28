<?php

declare(strict_types=1);

namespace Freemium\Repository;

class SubscriptionStubRepository implements SubscriptionRepository
{
    public function findBillable(): iterable
    {
    }

    public function findExpired(): iterable
    {
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
