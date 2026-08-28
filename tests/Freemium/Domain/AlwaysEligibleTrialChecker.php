<?php

declare(strict_types=1);

namespace Freemium\Domain;

class AlwaysEligibleTrialChecker implements TrialEligibilityChecker
{
    public function isEligibleForTrial(Subscribable $subscribable, SubscriptionPlan $plan): bool
    {
        return true;
    }
}
