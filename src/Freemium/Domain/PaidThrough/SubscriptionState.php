<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTimeImmutable;

class SubscriptionState
{
    private ?DateTimeImmutable $paidThrough;

    private ?bool $inTrial;

    private ?DateTimeImmutable $expireOn;

    private ?DateTimeImmutable $trialEndsOn;

    public function __construct(
        ?DateTimeImmutable $paidThrough = null,
        ?bool $inTrial = false,
        ?DateTimeImmutable $expires = null,
        ?DateTimeImmutable $trialEndsOn = null
    ) {
        $this->paidThrough = $paidThrough;
        $this->inTrial = $inTrial;
        $this->expireOn = $expires;
        $this->trialEndsOn = $trialEndsOn;
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

    public function getTrialEndsOn(): ?DateTimeImmutable
    {
        return $this->trialEndsOn;
    }
}
