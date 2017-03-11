<?php

namespace Freemium\Command;

use Freemium\FixturesHelper;
use Freemium\Event\EventProvider;
use Freemium\Command\Subscription\NewSubscription;
use Freemium\Command\Subscription\NewSubscriptionHandler;

class CommandBusTest extends \PHPUnit_Framework_TestCase
{
    use FixturesHelper;

    public function testCustomResolver()
    {
        $command = new NewSubscription(
            $this->users('bob'),
            $this->subscriptionPlans('free')
        );

        $eventProvider = new EventProvider();

        $commandBus = new CommandBus($eventProvider, function ($command, $eventProvider) {
            return new NewSubscriptionHandler($eventProvider);
        });

        $commandBus->handle($command);

        $events = $eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
    }
}
