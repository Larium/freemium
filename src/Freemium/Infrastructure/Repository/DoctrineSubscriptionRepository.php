<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Domain\Subscribable;
use Freemium\Domain\Subscription;
use Freemium\Domain\SubscriptionPlan;
use Freemium\Domain\SubscriptionStatus;

final class DoctrineSubscriptionRepository implements SubscriptionRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function hasCompletedOrUsedTrial(Subscribable $subscribable, SubscriptionPlan $plan): bool
    {
        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(s.token)')
            ->from(Subscription::class, 's')
            ->where('s.subscribable = :subscribable')
            ->andWhere('s.subscriptionPlan = :plan')
            ->andWhere('s.trialStartedOn IS NOT NULL')
            ->setParameter('subscribable', $subscribable)
            ->setParameter('plan', $plan)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function insert(Subscription $subscription): void
    {
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
    }

    public function update(Subscription $subscription): void
    {
        $this->entityManager->flush();
    }

    public function remove(Subscription $subscription): void
    {
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();
    }

    public function findByToken(string $token): ?Subscription
    {
        return $this->entityManager->find(Subscription::class, $token);
    }

    public function findBillable(): iterable
    {
        $today = new DateTimeImmutable('today');

        $subscriptions = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Subscription::class, 's')
            ->where('s.paidThrough IS NOT NULL')
            ->andWhere('s.paidThrough <= :today')
            ->andWhere('s.rate.amount > 0')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        foreach ($subscriptions as $subscription) {
            yield $subscription;
        }
    }

    public function findExpired(): iterable
    {
        $subscriptions = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Subscription::class, 's')
            ->where('s.status = :status')
            ->setParameter('status', SubscriptionStatus::CANCELED)
            ->getQuery()
            ->getResult();

        foreach ($subscriptions as $subscription) {
            yield $subscription;
        }
    }
}
