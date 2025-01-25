<?php

declare(strict_types=1);

namespace Freemium\Domain\Repository;

use Freemium\Domain\SubscriptionPlan;
use Freemium\Domain\Repository\Exception\EntityNotFoundException;

interface SubscriptionPlanRepository
{
    /**
     * @throws EntityNotFoundException
     */
    public function findByName(string $name): SubscriptionPlan;
}
