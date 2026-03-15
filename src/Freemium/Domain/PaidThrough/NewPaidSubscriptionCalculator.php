<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTimeImmutable;

class NewPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getState(): ?SubscriptionState
    {
        if ($this->getSubscription()->getOriginalPlan() === null) {
            $daysTrial = $this->getSubscription()->getDaysTrial();

            return new SubscriptionState(
                (new DateTimeImmutable('today'))->modify($daysTrial . ' days'),
                true,
                null
            );
        }

        return null;
    }
}
