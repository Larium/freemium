<?php

declare(strict_types=1);

namespace Freemium\Event;

class EventProvider
{
    private $events = [];

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function raise($event): void
    {
        $this->events[] = $event;
    }
}
