<?php

declare(strict_types=1);

namespace Freemium\Command;

use Freemium\Event\EventProvider;

class CommandBus
{
    private $resolver;

    private $eventProvider;

    public function __construct(
        EventProvider $eventProvider,
        callable $resolver
    ) {
        $this->resolver = $resolver;
        $this->eventProvider = $eventProvider;
    }

    public function handle(object $command)
    {
        return $this->resolveHandler($command)->handle($command);
    }

    private function resolveHandler(object $command)
    {
        $resolver = $this->resolver;

        return $resolver($command, $this->eventProvider);
    }
}
