<?php

declare(strict_types=1);

namespace Freemium\Domain;

interface TrialEligibilityChecker
{
    public function isEligibleForTrial(Subscribable $subscribable, SubscriptionPlan $plan): bool;
}
