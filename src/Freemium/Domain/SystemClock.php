<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTimeImmutable;

class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
