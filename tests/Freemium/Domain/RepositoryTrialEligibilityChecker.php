<?php

declare(strict_types=1);

namespace Freemium\Domain;

use Freemium\Domain\Repository\SubscriptionRepository;

/**
 * Test double: uses SubscriptionRepository::hasCompletedOrUsedTrial(Subscribable, SubscriptionPlan).
 * Ensures the repository interface is exercised so signature changes break tests.
 */
class RepositoryTrialEligibilityChecker implements TrialEligibilityChecker
{
    public function __construct(
        private readonly SubscriptionRepository $repository
    ) {
    }

    public function isEligibleForTrial(Subscribable $subscribable, SubscriptionPlan $plan): bool
    {
        return !$this->repository->hasCompletedOrUsedTrial($subscribable, $plan);
    }
}
