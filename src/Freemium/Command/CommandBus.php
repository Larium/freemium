<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Command;

class CommandBus
{
    private $resolver;

    public function __construct(callable $resolver = null)
    {
        $this->resolver = $resolver;
    }

    public function handle($command)
    {
        return $this->resolveHandler($command)->handle($command);
    }

    private function resolveHandler($command)
    {
        if (null === ($resolver = $this->resolver)) {
            $handlerClass = get_class($command) . 'Handler';

            return new $handlerClass();
        }

        return $resolver($command);
    }
}
