<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

class DefaultCalculator extends PaidThroughCalculator
{
    public function getState(): ?SubscriptionState
    {
        return new SubscriptionState(
            $this->getOn(),
            false,
            null
        );
    }
}
