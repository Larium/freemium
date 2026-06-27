<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Repository\Exception\EntityNotFoundException;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Subscribable;
use Freemium\Domain\Customer;

final class DoctrineSubscribableRepository implements SubscribableRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findByCustomerId(string $customerId): Subscribable
    {
        $customer = $this->entityManager->find(Customer::class, $customerId);
        if ($customer === null) {
            throw new EntityNotFoundException(sprintf('Customer "%s" not found.', $customerId));
        }

        return $customer;
    }

    public function insert(Subscribable $subscribable): void
    {
        $this->entityManager->persist($subscribable);
        $this->entityManager->flush();
    }
}
