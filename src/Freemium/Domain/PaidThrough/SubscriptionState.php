<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTime;

class SubscriptionState
{
    private $paidThrough;

    private $inTrial;

    private $expireOn;

    public function __construct(
        ?DateTime $paidThrough = null,
        ?bool $inTrial = false,
        ?DateTime $expires = null
    ) {
        $this->paidThrough = $paidThrough;
        $this->inTrial = $inTrial;
        $this->expireOn = $expires;
    }

    public function getPaidThrough(): ?DateTime
    {
        return $this->paidThrough;
    }

    public function isInTrial(): ?bool
    {
        return $this->inTrial;
    }

    public function getExpireOn(): ?DateTime
    {
        return $this->expireOn;
    }
}
