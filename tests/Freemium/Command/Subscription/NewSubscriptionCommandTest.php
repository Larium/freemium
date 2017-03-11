<?php

namespace Freemium\Command\Subscription;

use Freemium\FixturesHelper;
use Freemium\Command\CommandBus;
use Freemium\Event\EventProvider;
use Freemium\SubscriptionChangeInterface;
use Freemium\Event\Subscription\SubscriptionCreated;

class NewSubscriptionCommandTest extends \PHPUnit_Framework_TestCase
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

        $subscription = $this->handleCommand($command);

        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);

        $this->assertInstanceOf(SubscriptionCreated::class, $event);
        $this->assertEquals($subscription, $event->getSubscription());
        $this->assertEquals(SubscriptionCreated::NAME, $event->getName());
    }

    private function handleCommand($command)
    {
        return $this->getCommandBus()->handle($command);
    }

    public function getCommandBus()
    {
        return new CommandBus($this->eventProvider);
    }
}
