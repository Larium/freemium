<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTimeImmutable;

class DefaultCalculator extends PaidThroughCalculator
{
    public function getState(): ?SubscriptionState
    {
        return new SubscriptionState(
            new DateTimeImmutable('today'),
            false,
            null
        );
    }
}
