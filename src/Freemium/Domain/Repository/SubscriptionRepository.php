<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\SubscriptionPlan;
use Freemium\Domain\Subscribable;
use Freemium\Domain\Transaction;
use Freemium\Domain\Subscription;

interface SubscriptionRepository
{
    /**
     * Whether the subscribable has completed or used a trial for the given plan.
     */
    public function hasCompletedOrUsedTrial(Subscribable $subscribable, SubscriptionPlan $plan): bool;

    public function insert(Subscription $subscription): void;

    public function update(Subscription $subscription): void;

    public function remove(Subscription $subscription): void;

    public function findByToken(string $token): ?Subscription;

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
