<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use Freemium\Domain\Subscription;

class ChargeSubscription
{
    public function __construct(
        private readonly Subscription $subscription,
        private readonly ?string $idempotencyKey = null
    ) {
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
