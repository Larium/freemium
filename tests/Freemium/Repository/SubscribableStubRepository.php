<?php

declare(strict_types=1);

namespace Freemium\Repository;

use Freemium\SubscribableInterface;
use Freemium\Repository\SubscribableRepositoryInterface;

class SubscribableStubRepository implements SubscribableRepositoryInterface
{
    private $storage;

    public function insert(SubscribableInterface $subscribable): void
    {
        $this->storage[] = $subscribable;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}
