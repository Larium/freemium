<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Repository\SubscriptionChangeRepository;
use Freemium\Domain\SubscriptionChange;

final class DoctrineSubscriptionChangeRepository implements SubscriptionChangeRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function insert(SubscriptionChange $change): void
    {
        $this->entityManager->persist($change);
        $this->entityManager->flush();
    }
}
