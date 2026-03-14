<?php

declare(strict_types=1);

namespace Freemium\Domain;

class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}
