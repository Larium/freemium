<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChangePlan;

use PHPUnit\Framework\TestCase;
use Freemium\Domain\Subscription;
use Freemium\Domain\FixturesHelper;
use Freemium\Domain\SubscriptionPlan;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscriptionStubRepository;
use Freemium\Application\UseCase\ChangePlan\Event\SubscriptionChanged;
use Freemium\Application\UseCase\ChangePlan\Event\SubscriptionNotChanged;

class ChangePlanHandlerTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    protected function setUp(): void
    {
        $this->fixturesSetUp();
        $this->eventProvider = new EventProvider();
    }

    public function testSuccessChangeHandler(): void
    {
        $command = new ChangePlan(
            $this->subscriptions('testChangePlan'),
            $this->subscriptionPlans('premium')
        );

        $event = $this->handleResult($command, SubscriptionChanged::class);
        $this->assertInstanceOf(SubscriptionChanged::class, $event);

        $updated = $event->getSubscription();
        $this->assertSame($this->subscriptionPlans('premium'), $updated->getSubscriptionPlan());
        $this->assertSame($this->subscriptionPlans('basic'), $updated->getOriginalPlan());
        $this->assertNotNull($updated->getPaidThrough());
        $this->assertGreaterThanOrEqual(1, $updated->getRemainingDays(), 'Value-preserving conversion should give at least 1 day');
    }

    public function testFailedChangeHandler(): void
    {
        $command = new ChangePlan(
            $this->subscriptions('testChangePlanNoBillingKey'),
            $this->subscriptionPlans('premium')
        );

        $this->handleResult($command, SubscriptionNotChanged::class);
    }

    private function handleResult(ChangePlan $command, string $eventClass): SubscriptionChanged|SubscriptionNotChanged
    {
        try {
            $this->createHandler()->handle($command);
        } catch (\DomainException $e) {
            // continue
        }
        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);

        $subscription = $event->getSubscription();

        $this->assertInstanceOf($eventClass, $event);
        $this->assertInstanceOf(Subscription::class, $subscription);

        if ($eventClass === SubscriptionNotChanged::class) {
            $this->assertInstanceOf(\DomainException::class, $event->getException());
            $this->assertInstanceOf(SubscriptionPlan::class, $event->getSubscriptionPlan());
        }

        return $event;
    }

    private function createHandler()
    {
        return new ChangePlanHandler(
            $this->eventProvider,
            new SubscriptionStubRepository()
        );
    }
}
