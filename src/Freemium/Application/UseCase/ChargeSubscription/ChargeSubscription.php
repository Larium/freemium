<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use Freemium\Domain\Subscription;

class ChargeSubscription
{
    public function __construct(
        private readonly Subscription $subscription
    ) {
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
