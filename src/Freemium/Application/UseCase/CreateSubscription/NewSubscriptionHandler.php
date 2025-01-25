<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\CreateSubscription;

use Freemium\Domain\Subscription;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;
use Freemium\Domain\Repository\SubscriptionPlanRepository;

class NewSubscriptionHandler extends AbstractCommandHandler
{
    public function __construct(
        EventProvider $eventProvider,
        private readonly SubscriptionRepository $repository,
        private readonly SubscribableRepository $subscribableRepository,
        private readonly SubscriptionPlanRepository $subscriptionPlanRepository
    ) {
        parent::__construct($eventProvider);
    }

    public function handle(NewSubscription $command): void
    {
        $subscribable = $this->subscribableRepository->findByCustomerId($command->getCustomerId());
        $subscriptionPlan = $this->subscriptionPlanRepository->findByName($command->getSubscriptionPlan());

        $subscription = new Subscription(
            $subscribable,
            $subscriptionPlan
        );

        $this->repository->insert($subscription);

        $this->getEventProvider()->raise(new Event\SubscriptionCreated($subscription));
    }
}
