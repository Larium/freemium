<?php

declare(strict_types=1);

namespace Freemium\Command\ChangePlan;

use Throwable;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Event\EventProvider;
use Freemium\Repository\SubscriptionRepositoryInterface;

class ChangePlanHandler extends AbstractCommandHandler
{
    private $repository;

    public function __construct(
        EventProvider $eventProvider,
        SubscriptionRepositoryInterface $repository
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
    }

    public function handle(ChangePlan $command)
    {
        $subscription = $command->getSubscription();
        $plan = $command->getSubscriptionPlan();

        $event = new Event\SubscriptionChanged($subscription);

        try {
            $subscription->setSubscriptionPlan($plan);
            $this->repository->update($subscription);
        } catch (Throwable $e) {
            $event = new Event\SubscriptionNotChanged($subscription, $plan, $e);
            throw $e;
        } finally {
            $this->getEventProvider()->raise($event);
        }
    }
}
