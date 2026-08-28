<?php

declare(strict_types=1);

namespace Freemium\Domain\Gateways;

use Freemium\Domain\Subscribable;

class BogusGatewayFactory implements GatewayFactory
{
    public function getGatewayFor(Subscribable $subscribable): GatewayInterface
    {
        return new Bogus();
    }
}
