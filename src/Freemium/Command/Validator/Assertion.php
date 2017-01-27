<?php

namespace Freemium\Command\Validator;

use InvalidArgumentException;

trait Assertion
{
    protected function assertInstanceOf($class, $instance)
    {
        if (!($instance instanceof $class)) {
            throw new InvalidArgumentException(
                sprintf('Expect instance of `%s`, `%s` given.', $class, get_class($instance))
            );
        }
    }
}
