<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

use DateTime;
use Freemium\Freemium;

class NewPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getState(): ?SubscriptionState
    {
        if ($this->getSubscription()->getOriginalPlan() === null) {
            return new SubscriptionState(
                (new DateTime('today'))->modify(Freemium::$daysFreeTrial . ' days'),
                true,
                null
            );
        }

        return null;
    }
}
