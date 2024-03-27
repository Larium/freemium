<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

use DateTime;

class DefaultCalculator extends PaidThroughCalculator
{
    public function getPaidThrough(): ?PaidThrough
    {
        return new PaidThrough(
            new DateTime('today'),
            false,
            null
        );
    }
}
