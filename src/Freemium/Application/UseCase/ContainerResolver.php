<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase;

use ReflectionClass;
use Psr\Container\ContainerInterface;

final class ContainerResolver
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(object $command): object
    {
        $commandReflection = new ReflectionClass($command);
        $handlerClass = $commandReflection->getNamespaceName()
            . '\\'
            . $commandReflection->getShortName()
            . 'Handler';

        return $this->container->get($handlerClass);
    }
}
