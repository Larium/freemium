<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

class NotPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getPaidThrough(): ?PaidThrough
    {
        if (!$this->getSubscription()->isPaid()) {
            return new PaidThrough(
                null,
                false,
                null
            );
        }

        return null;
    }
}
