<?php

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\FixturesHelper;
use PHPUnit\Framework\TestCase;
use Freemium\Event\EventProvider;
use Freemium\SubscriptionChangeInterface;
use Freemium\Repository\SubscriptionStubRepository;

class NewSubscriptionCommandTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    protected function setUp(): void
    {
        $this->fixturesSetUp();
        $this->eventProvider = new EventProvider();
    }

    public function testNewSubscriptionCreated()
    {
        $command = new NewSubscription(
            $this->users('bob'),
            $this->subscriptionPlans('free')
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
            new SubscriptionStubRepository()
        );
    }
}
