<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase;

class CommandBus
{
    private readonly mixed $resolver;

    public function __construct(
        callable $resolver
    ) {
        $this->resolver = $resolver;
    }

    public function handle(object $command)
    {
        return $this->resolveHandler($command)->handle($command);
    }

    private function resolveHandler(object $command)
    {
        $resolver = $this->resolver;

        return $resolver($command);
    }
}
