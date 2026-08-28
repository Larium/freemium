<?php

namespace Freemium\Application\UseCase;

use Freemium\Application\Event\EventProvider;

abstract class AbstractCommandHandler
{
    public function __construct(
        private readonly EventProvider $eventProvider
    ) {
    }

    public function getEventProvider(): EventProvider
    {
        return $this->eventProvider;
    }
}
