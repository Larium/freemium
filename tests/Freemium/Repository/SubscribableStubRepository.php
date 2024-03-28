<?php

declare(strict_types=1);

namespace Freemium\Repository;

use Freemium\Subscribable;
use Freemium\Repository\SubscribableRepository;

class SubscribableStubRepository implements SubscribableRepository
{
    private $storage;

    public function insert(Subscribable $subscribable): void
    {
        $this->storage[] = $subscribable;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}
