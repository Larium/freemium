<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\CreateSubscription;

use DomainException;
use Freemium\Domain\Clock;
use Freemium\Domain\Subscription;
use Freemium\Domain\SubscriptionChange;
use Freemium\Domain\SubscriptionChangeReason;
use Freemium\Domain\SystemClock;
use Freemium\Domain\TrialEligibilityChecker;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionChangeRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;
use Freemium\Domain\Repository\SubscriptionPlanRepository;

class NewSubscriptionHandler extends AbstractCommandHandler
{
    public function __construct(
        EventProvider $eventProvider,
        private readonly SubscriptionRepository $repository,
        private readonly SubscriptionChangeRepository $subscriptionChangeRepository,
        private readonly SubscribableRepository $subscribableRepository,
        private readonly SubscriptionPlanRepository $subscriptionPlanRepository,
        private readonly TrialEligibilityChecker $trialEligibilityChecker,
        private readonly Clock $clock = new SystemClock()
    ) {
        parent::__construct($eventProvider);
    }

    public function handle(NewSubscription $command): void
    {
        $subscribable = $this->subscribableRepository->findByCustomerId($command->getCustomerId());
        $subscriptionPlan = $this->subscriptionPlanRepository->findByName($command->getSubscriptionPlan());

        $daysTrial = $command->getDaysTrial();
        if ($daysTrial > 0 && !$this->trialEligibilityChecker->isEligibleForTrial($subscribable, $subscriptionPlan)) {
            throw new DomainException('Subscribable is not eligible for a trial.');
        }

        $on = $this->clock->now()->setTime(0, 0, 0);
        $subscription = new Subscription(
            $command->getToken(),
            $subscribable,
            $subscriptionPlan,
            $on,
            $daysTrial,
            $command->getDaysGrace()
        );

        $this->repository->insert($subscription);
        $this->subscriptionChangeRepository->insert(
            new SubscriptionChange($subscription, SubscriptionChangeReason::REASON_NEW, null)
        );

        $this->getEventProvider()->raise(new Event\SubscriptionCreated($subscription));
    }
}
