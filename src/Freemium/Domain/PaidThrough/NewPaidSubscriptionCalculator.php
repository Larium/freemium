<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

class NewPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getState(): ?SubscriptionState
    {
        if ($this->getSubscription()->getOriginalPlan() === null) {
            $trialDays = $this->getSubscription()->getSubscriptionPlan()->getTrialDays();
            $trialEndsOn = $this->getOn()->modify($trialDays . ' days');

            return new SubscriptionState(
                $trialEndsOn,
                true,
                null,
                $trialEndsOn
            );
        }

        return null;
    }
}
