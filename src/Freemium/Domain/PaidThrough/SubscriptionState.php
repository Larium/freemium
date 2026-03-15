<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTimeImmutable;

class SubscriptionState
{
    private ?DateTimeImmutable $paidThrough;

    private ?bool $inTrial;

    private ?DateTimeImmutable $expireOn;

    public function __construct(
        ?DateTimeImmutable $paidThrough = null,
        ?bool $inTrial = false,
        ?DateTimeImmutable $expires = null
    ) {
        $this->paidThrough = $paidThrough;
        $this->inTrial = $inTrial;
        $this->expireOn = $expires;
    }

    public function getPaidThrough(): ?DateTimeImmutable
    {
        return $this->paidThrough;
    }

    public function isInTrial(): ?bool
    {
        return $this->inTrial;
    }

    public function getExpireOn(): ?DateTimeImmutable
    {
        return $this->expireOn;
    }
}
