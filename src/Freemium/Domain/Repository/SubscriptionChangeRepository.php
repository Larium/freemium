<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\SubscriptionChange;

interface SubscriptionChangeRepository
{
    public function insert(SubscriptionChange $change): void;
}
