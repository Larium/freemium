<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

use DateTime;

class PaidThrough
{
    private $date;

    private $inTrial;

    private $expireOn;

    public function __construct(?DateTime $date, ?bool $inTrial, ?DateTime $expires)
    {
        $this->date = $date;
        $this->inTrial = $inTrial;
        $this->expireOn = $expires;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
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
