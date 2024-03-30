<?php

declare(strict_types=1);

namespace Freemium\Repository;

use Freemium\SubscriptionPlan;
use Freemium\Repository\Exception\EntityNotFoundException;

interface SubscriptionPlanRepository
{
    /**
     * @throws EntityNotFoundException
     */
    public function findByName(string $name): SubscriptionPlan;
}
