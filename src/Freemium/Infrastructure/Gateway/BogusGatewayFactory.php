<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Gateway;

use Freemium\Domain\Gateways\Bogus;
use Freemium\Domain\Gateways\GatewayFactory;
use Freemium\Domain\Gateways\GatewayInterface;
use Freemium\Domain\Subscribable;

final class BogusGatewayFactory implements GatewayFactory
{
    public function getGatewayFor(Subscribable $subscribable): GatewayInterface
    {
        return new Bogus();
    }
}
