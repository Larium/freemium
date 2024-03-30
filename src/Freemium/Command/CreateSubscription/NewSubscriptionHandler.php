<?php

declare(strict_types=1);

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Repository\SubscribableRepository;
use Freemium\Repository\SubscriptionRepository;
use Freemium\Repository\SubscriptionPlanRepository;

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
