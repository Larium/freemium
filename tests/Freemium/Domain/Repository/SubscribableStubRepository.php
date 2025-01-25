<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\Subscribable;
use Freemium\Domain\Repository\SubscribableRepository;

class SubscribableStubRepository implements SubscribableRepository
{
    private $storage;

    public function findByCustomerId(string $customerId): Subscribable
    {
        return reset($this->storage);
    }

    public function insert(Subscribable $subscribable): void
    {
        $this->storage[] = $subscribable;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}
