<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\CreateSubscription;

use PHPUnit\Framework\TestCase;
use Freemium\Domain\Subscription;
use Freemium\Domain\FixturesHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionChangeStubRepository;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\Repository\SubscriptionStubRepository;
use Freemium\Domain\AlwaysEligibleTrialChecker;
use Freemium\Domain\RepositoryTrialEligibilityChecker;
use Freemium\Infrastructure\Service\CustomIdGenerator;

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

    public function testNewSubscriptionWithTrialWhenNotEligible_throws(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findByCustomerId')
            ->willReturn($this->users('bob'));
        $this->subscriptionPlanRepository->expects($this->once())
            ->method('findByName')
            ->willReturn($this->subscriptionPlans('basic'));

        $checker = $this->createMock(\Freemium\Domain\TrialEligibilityChecker::class);
        $checker->method('isEligibleForTrial')->willReturn(false);

        $handler = new NewSubscriptionHandler(
            $this->eventProvider,
            new SubscriptionStubRepository(),
            new SubscriptionChangeStubRepository(),
            $this->userRepository,
            $this->subscriptionPlanRepository,
            new CustomIdGenerator(),
            $checker
        );

        $command = new NewSubscription('cus_123', 'basic', 14, 3);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('not eligible for a trial');
        $handler->handle($command);
    }

    public function testNewSubscriptionWithTrialWhenEligible_createsWithTrial(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findByCustomerId')
            ->willReturn($this->users('bob'));
        $this->subscriptionPlanRepository->expects($this->once())
            ->method('findByName')
            ->willReturn($this->subscriptionPlans('basic'));

        $command = new NewSubscription('cus_123', 'basic', 14, 3);
        $this->handleCommand($command);

        $events = $this->eventProvider->releaseEvents();
        $this->assertCount(1, $events);
        $subscription = $events[0]->getSubscription();
        $this->assertSame(14, $subscription->getDaysTrial());
        $this->assertSame(3, $subscription->getDaysGrace());
    }

    /**
     * Uses RepositoryTrialEligibilityChecker so SubscriptionRepository::hasCompletedOrUsedTrial(Subscribable, SubscriptionPlan) is called.
     * If the repository interface changes, this test fails.
     */
    public function testNewSubscriptionWithTrial_usesRepositoryHasCompletedOrUsedTrial(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findByCustomerId')
            ->willReturn($this->users('bob'));
        $this->subscriptionPlanRepository->expects($this->once())
            ->method('findByName')
            ->willReturn($this->subscriptionPlans('basic'));

        $subscriptionRepository = new SubscriptionStubRepository();
        $subscriptionRepository->setHasCompletedOrUsedTrial(true);

        $handler = new NewSubscriptionHandler(
            $this->eventProvider,
            $subscriptionRepository,
            new SubscriptionChangeStubRepository(),
            $this->userRepository,
            $this->subscriptionPlanRepository,
            new CustomIdGenerator(),
            new RepositoryTrialEligibilityChecker($subscriptionRepository)
        );

        $command = new NewSubscription('cus_123', 'basic', 14, 3);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('not eligible for a trial');
        $handler->handle($command);
    }

    public function createHandler()
    {
        return new NewSubscriptionHandler(
            $this->eventProvider,
            new SubscriptionStubRepository(),
            new SubscriptionChangeStubRepository(),
            $this->userRepository,
            $this->subscriptionPlanRepository,
            new CustomIdGenerator(),
            new AlwaysEligibleTrialChecker()
        );
    }
}
