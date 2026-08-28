<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Repository\TransactionRepository;
use Freemium\Domain\Transaction;

final class DoctrineTransactionRepository implements TransactionRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findByIdempotencyKey(string $idempotencyKey): ?Transaction
    {
        return $this->entityManager->getRepository(Transaction::class)->findOneBy(['idempotencyKey' => $idempotencyKey]);
    }

    public function insert(Transaction $transaction): void
    {
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    public function update(Transaction $transaction): void
    {
        $this->entityManager->flush();
    }
}
