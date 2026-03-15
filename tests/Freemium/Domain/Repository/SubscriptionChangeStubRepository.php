<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\SubscriptionChange;

class SubscriptionChangeStubRepository implements SubscriptionChangeRepository
{
    public function insert(SubscriptionChange $change): void
    {
        // No-op for stub
    }
}
