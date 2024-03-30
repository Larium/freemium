<?php

declare(strict_types=1);

namespace Freemium\Repository;

use Freemium\Subscribable;

interface SubscribableRepository
{
    public function findByCustomerId(string $customerId): Subscribable;

    public function insert(Subscribable $subscribable): void;
}
