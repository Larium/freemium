<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase;

use PHPUnit\Framework\TestCase;
use Freemium\Domain\FixturesHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\AlwaysEligibleTrialChecker;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\Repository\SubscriptionStubRepository;
use Freemium\Domain\Repository\SubscriptionChangeStubRepository;
use Freemium\Application\UseCase\CreateSubscription\NewSubscription;
use Freemium\Application\UseCase\CreateSubscription\NewSubscriptionHandler;

class CommandBusTest extends TestCase
{
    use FixturesHelper;

    private SubscribableRepository|MockObject $userRepository;

    private SubscriptionPlanRepository|MockObject $subscriptionPlanRepository;

    public function setUp(): void
    {
        $this->fixturesSetUp();
        $this->userRepository = $this->createMock(SubscribableRepository::class);
        $this->subscriptionPlanRepository = $this->createMock(SubscriptionPlanRepository::class);
    }

    public function testCustomResolver()
    {
        $this->userRepository->expects($this->once())
            ->method('findByCustomerId')
            ->willReturn($this->users('bob'));

        $this->subscriptionPlanRepository->expects($this->once())
            ->method('findByName')
            ->willReturn($this->subscriptionPlans('free'));

        $command = new NewSubscription(
            'sub_test',
            'bob',
            'free'
        );

        $eventProvider = new EventProvider();

        $commandBus = new CommandBus(function ($command) use ($eventProvider) {
            return new NewSubscriptionHandler(
                $eventProvider,
                new SubscriptionStubRepository(),
                new SubscriptionChangeStubRepository(),
                $this->userRepository,
                $this->subscriptionPlanRepository,
                new AlwaysEligibleTrialChecker()
            );
        });

        $commandBus->handle($command);

        $events = $eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
    }
}
