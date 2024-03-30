<?php

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\FixturesHelper;
use PHPUnit\Framework\TestCase;
use Freemium\Event\EventProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Freemium\Repository\SubscribableRepository;
use Freemium\Repository\SubscriptionPlanRepository;
use Freemium\Repository\SubscriptionStubRepository;

class NewSubscriptionCommandTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    private SubscribableRepository|MockObject $userRepository;

    private SubscriptionPlanRepository|MockObject $subscriptionPlanRepository;

    protected function setUp(): void
    {
        $this->fixturesSetUp();
        $this->eventProvider = new EventProvider();
        $this->userRepository = $this->createMock(SubscribableRepository::class);
        $this->subscriptionPlanRepository = $this->createMock(SubscriptionPlanRepository::class);
    }

    public function testNewSubscriptionCreated()
    {
        $this->userRepository->expects($this->once())
            ->method('findByCustomerId')
            ->willReturn($this->users('bob'));

        $this->subscriptionPlanRepository->expects($this->once())
            ->method('findByName')
            ->willReturn($this->subscriptionPlans('free'));

        $command = new NewSubscription(
            'cus_123',
            'free'
        );

        $this->handleCommand($command);

        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);

        $this->assertInstanceOf(Event\SubscriptionCreated::class, $event);
        $this->assertInstanceOf(Subscription::class, $event->getSubscription());
        $this->assertEquals(Event\SubscriptionCreated::NAME, $event->getName());
    }

    private function handleCommand($command)
    {
        return $this->createHandler()->handle($command);
    }

    public function createHandler()
    {
        return new NewSubscriptionHandler(
            $this->eventProvider,
            new SubscriptionStubRepository(),
            $this->userRepository,
            $this->subscriptionPlanRepository
        );
    }
}
