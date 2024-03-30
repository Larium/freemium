<?php

declare(strict_types=1);

namespace Freemium\Event;

abstract class DomainEvent
{
    public const NAME = 'default';

    public function getName(): string
    {
        return static::NAME;
    }
}
