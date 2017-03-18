<?php

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\FixturesHelper;
use Freemium\Event\EventProvider;
use Freemium\SubscriptionChangeInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Freemium\Repository\SubscriptionStubRepository;
use Freemium\Event\Subscription\SubscriptionCreated;

class NewSubscriptionCommandTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    protected function setUp()
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

        $this->assertInstanceOf(SubscriptionCreated::class, $event);
        $this->assertInstanceOf(Subscription::class, $event->getSubscription());
        $this->assertEquals(SubscriptionCreated::NAME, $event->getName());
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
