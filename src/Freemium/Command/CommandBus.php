<?php

namespace Freemium\Command;

use Freemium\Event\EventProvider;

class CommandBus
{
    private $resolver;

    private $eventProvider;

    public function __construct(
        EventProvider $eventProvider,
        callable $resolver = null
    ) {
        $this->resolver = $resolver;
        $this->eventProvider = $eventProvider;
    }

    public function handle($command)
    {
        return $this->resolveHandler($command)->handle($command);
    }

    private function resolveHandler($command)
    {
        if (null === ($resolver = $this->resolver)) {
            $handlerClass = get_class($command) . 'Handler';

            return new $handlerClass($this->eventProvider);
        }

        return $resolver($command, $this->eventProvider);
    }
}
