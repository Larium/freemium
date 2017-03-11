<?php

namespace Freemium\Event;

abstract class DomainEvent
{
    public function getName()
    {
        return static::NAME;
    }
}
