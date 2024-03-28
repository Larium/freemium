<?php

declare(strict_types=1);

namespace Freemium\Repository;

use Freemium\Subscribable;

interface SubscribableRepository
{
    public function insert(Subscribable $subscribable): void;
}
