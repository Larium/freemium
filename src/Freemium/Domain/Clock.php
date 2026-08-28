<?php

declare(strict_types=1);

namespace Freemium\Domain;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
