<?php

declare(strict_types=1);

namespace Freemium\Domain\Gateways;

use Freemium\Domain\Subscribable;

interface GatewayFactory
{
    public function getGatewayFor(Subscribable $subscribable): GatewayInterface;
}
