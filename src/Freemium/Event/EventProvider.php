<?php

namespace Freemium\Event;

class EventProvider
{
    private $events = [];

    public function releaseEvents()
    {
        $events = $this->events;
        $this->events = array();

        return $events;
    }

    public function raise($event)
    {
        $this->events[] = $event;
    }
}
