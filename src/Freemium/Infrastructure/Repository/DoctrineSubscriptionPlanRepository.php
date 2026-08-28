<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Repository\Exception\EntityNotFoundException;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\SubscriptionPlan;

final class DoctrineSubscriptionPlanRepository implements SubscriptionPlanRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findByToken(string $token): ?SubscriptionPlan
    {
        return $this->entityManager->find(SubscriptionPlan::class, $token);
    }

    public function findByName(string $name): SubscriptionPlan
    {
        $plan = $this->entityManager->getRepository(SubscriptionPlan::class)->findOneBy(['name' => $name]);
        if ($plan === null) {
            throw new EntityNotFoundException(sprintf('Subscription plan "%s" not found.', $name));
        }

        return $plan;
    }
}
