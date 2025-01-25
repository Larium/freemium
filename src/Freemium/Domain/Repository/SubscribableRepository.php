<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Subscribable;

interface SubscribableRepository
{
    public function findByCustomerId(string $customerId): Subscribable;

    public function insert(Subscribable $subscribable): void;
}
