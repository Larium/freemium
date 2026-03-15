<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChangePlan;

use Throwable;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscriptionChangeRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;

class ChangePlanHandler extends AbstractCommandHandler
{
    private $repository;

    private SubscriptionChangeRepository $subscriptionChangeRepository;

    public function __construct(
        EventProvider $eventProvider,
        SubscriptionRepository $repository,
        SubscriptionChangeRepository $subscriptionChangeRepository
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
        $this->subscriptionChangeRepository = $subscriptionChangeRepository;
    }

    public function handle(ChangePlan $command)
    {
        $subscription = $command->getSubscription();
        $plan = $command->getSubscriptionPlan();

        $event = new Event\SubscriptionChanged($subscription);

        try {
            $change = $subscription->setSubscriptionPlan($plan);
            if ($change !== null) {
                $this->subscriptionChangeRepository->insert($change);
            }
            $this->repository->update($subscription);
        } catch (Throwable $e) {
            $event = new Event\SubscriptionNotChanged($subscription, $plan, $e);
            throw $e;
        } finally {
            $this->getEventProvider()->raise($event);
        }
    }
}
