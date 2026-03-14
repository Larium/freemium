<?php

declare(strict_types=1);

namespace Freemium\Domain;

/**
 * Test double: returns a fixed moment in time for deterministic time-based tests.
 */
final class FrozenClock implements Clock
{
    public function __construct(
        private readonly \DateTimeImmutable $fixedNow
    ) {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->fixedNow;
    }
}
