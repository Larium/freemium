<?php

declare(strict_types=1);

namespace Freemium\Event;

abstract class DomainEvent
{
    public function getName(): string
    {
        return static::NAME;
    }
}
