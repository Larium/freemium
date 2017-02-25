<?php

namespace Freemium\Command;

use Freemium\Event\EventProvider;

abstract class AbstractCommandHandler
{
    private $eventProvider;

    public function __construct(EventProvider $eventProvider)
    {
        $this->eventProvider = $eventProvider;
    }

    public function getEventProvider()
    {
        return $this->eventProvider;
    }
}
