<?php

declare(strict_types=1);

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
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

    public function handle(NewSubscription $command) : void
    {
        $subscription = new Subscription(
            $command->getSubscribable(),
            $command->getSubscriptionPlan()
        );

        $this->getEventProvider()->raise(new Event\SubscriptionCreated($subscription));

        $this->repository->insert($subscription);
    }
}
