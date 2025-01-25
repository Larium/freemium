<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTime;

class DefaultCalculator extends PaidThroughCalculator
{
    public function getState(): ?SubscriptionState
    {
        return new SubscriptionState(
            new DateTime('today'),
            false,
            null
        );
    }
}
