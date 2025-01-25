<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

class SubscriptionStubRepository implements SubscriptionRepository
{
    public function findBillable(): iterable
    {
        return [];
    }

    public function findExpired(): iterable
    {
        return [];
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
