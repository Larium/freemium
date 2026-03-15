<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTime;

class NewPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getState(): ?SubscriptionState
    {
        if ($this->getSubscription()->getOriginalPlan() === null) {
            $daysTrial = $this->getSubscription()->getDaysTrial();

            return new SubscriptionState(
                (new DateTime('today'))->modify($daysTrial . ' days'),
                true,
                null
            );
        }

        return null;
    }
}
