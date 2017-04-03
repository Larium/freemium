<?php

declare(strict_types = 1);

namespace Freemium\Repository;

use Freemium\SubscribableInterface;

interface SubscribableRepositoryInterface
{
    public function insert(SubscribableInterface $subscribable) : void;
}
