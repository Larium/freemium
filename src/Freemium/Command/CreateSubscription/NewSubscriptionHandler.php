<?php

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Event\Subscription\SubscriptionCreated;
use Freemium\Repository\SubscriptionRepositoryInterface;

class NewSubscriptionHandler extends AbstractCommandHandler
{
    private $repository;

    public function __construct(
        EventProvider $eventProvider,
        SubscriptionRepositoryInterface $repository
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
    }

    public function handle(NewSubscription $command)
    {
        return $this->createSubscription($command);
    }

    private function createSubscription($command)
    {
        $subscription = new Subscription(
            $command->getSubscribable(),
            $command->getSubscriptionPlan()
        );

        $this->getEventProvider()->raise(new SubscriptionCreated($subscription));

        $this->repository->insert($subscription);
    }
}
