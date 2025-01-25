<?php

namespace Freemium\Application\UseCase;

use Freemium\Application\Event\EventProvider;

abstract class AbstractCommandHandler
{
    private $eventProvider;

    public function __construct(EventProvider $eventProvider)
    {
        $this->eventProvider = $eventProvider;
    }

    public function getEventProvider(): EventProvider
    {
        return $this->eventProvider;
    }
}
