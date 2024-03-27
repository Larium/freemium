<?php

declare(strict_types=1);

namespace Freemium\Command\ChangePlan;

use Freemium\Subscription;
use Freemium\FixturesHelper;
use Freemium\SubscriptionPlan;
use PHPUnit\Framework\TestCase;
use Freemium\Event\EventProvider;
use Freemium\Repository\SubscriptionStubRepository;
use Freemium\Command\ChangePlan\Event\SubscriptionChanged;
use Freemium\Command\ChangePlan\Event\SubscriptionNotChanged;

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

        $this->handleResult($command, SubscriptionChanged::class);
    }

    public function testFailedChangeHandler(): void
    {
        $command = new ChangePlan(
            $this->subscriptions('testChangePlanNoBillingKey'),
            $this->subscriptionPlans('premium')
        );

        $this->handleResult($command, SubscriptionNotChanged::class);
    }

    private function handleResult(ChangePlan $command, string $eventClass)
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
    }

    private function createHandler()
    {
        return new ChangePlanHandler(
            $this->eventProvider,
            new SubscriptionStubRepository()
        );
    }
}
