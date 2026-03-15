<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\SubscriptionPlan;
use Freemium\Domain\Subscribable;
use Freemium\Domain\Transaction;
use Freemium\Domain\Subscription;

class SubscriptionStubRepository implements SubscriptionRepository
{
    private bool $hasCompletedOrUsedTrial = false;

    public function setHasCompletedOrUsedTrial(bool $value): void
    {
        $this->hasCompletedOrUsedTrial = $value;
    }

    public function hasCompletedOrUsedTrial(Subscribable $subscribable, SubscriptionPlan $plan): bool
    {
        return $this->hasCompletedOrUsedTrial;
    }

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
