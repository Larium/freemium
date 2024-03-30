<?php

namespace Freemium\Command;

use Freemium\FixturesHelper;
use PHPUnit\Framework\TestCase;
use Freemium\Event\EventProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Freemium\Repository\SubscribableRepository;
use Freemium\Repository\SubscriptionPlanRepository;
use Freemium\Repository\SubscriptionStubRepository;
use Freemium\Command\CreateSubscription\NewSubscription;
use Freemium\Command\CreateSubscription\NewSubscriptionHandler;

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
            'bob',
            'free'
        );

        $eventProvider = new EventProvider();

        $commandBus = new CommandBus($eventProvider, function ($command, $eventProvider) {
            return new NewSubscriptionHandler(
                $eventProvider,
                new SubscriptionStubRepository(),
                $this->userRepository,
                $this->subscriptionPlanRepository
            );
        });

        $commandBus->handle($command);

        $events = $eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
    }
}
