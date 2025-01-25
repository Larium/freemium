<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

class NotPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getState(): ?SubscriptionState
    {
        if (!$this->getSubscription()->isPaid()) {
            return new SubscriptionState();
        }

        return null;
    }
}
