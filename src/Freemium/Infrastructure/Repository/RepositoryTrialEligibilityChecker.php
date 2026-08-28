<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Domain\Subscribable;
use Freemium\Domain\SubscriptionPlan;
use Freemium\Domain\TrialEligibilityChecker;

final class RepositoryTrialEligibilityChecker implements TrialEligibilityChecker
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
