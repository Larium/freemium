<?php

declare(strict_types=1);

namespace Freemium\Command\ChargeSubscription;

use Freemium\Freemium;
use Freemium\Subscription;
use Freemium\FixturesHelper;
use Freemium\Event\EventProvider;
use PHPUnit_Framework_TestCase as TestCase;
use Freemium\Repository\SubscriptionStubRepository;

class ChargeSubscriptionHandlerTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    protected function setUp()
    {
        $this->fixturesSetUp();
        $this->eventProvider = new EventProvider();
    }

    public function testSuccessChargeHandle()
    {
        $command = new ChargeSubscription(
            $this->subscriptions('testChargePaidSubscription')
        );

        $this->handleResult($command, Event\SubscriptionPaid::class);
    }

    public function testHandleExpiredSubscription()
    {
        $command = new ChargeSubscription(
            $this->subscriptions('testExpiration')
        );

        Freemium::setExpiredPlan($this->subscriptionPlans('free'));

        $this->handleResult($command, Event\SubscriptionExpired::class);
    }

    public function testHandleInGraceSubscription()
    {
        $command = new ChargeSubscription(
            $this->subscriptions('testInGraceSubscription')
        );

        $this->handleResult($command, Event\SubscriptionGraced::class);
    }

    private function handleResult($command, $eventClass)
    {
        $this->createHandler()->handle($command);
        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);

        $subscription = $event->getSubscription();

        $this->assertInstanceOf($eventClass, $event);
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertNotNull($subscription->getLastTransactionAt());
        $this->assertNotEmpty($subscription->getTransactions());

        if ($eventClass === Event\SubscriptionExpired::class) {
            $this->assertNotNull($subscription->getExpireOn());
        }
    }

    private function createHandler()
    {
        return new ChargeSubscriptionHandler(
            $this->eventProvider,
            new SubscriptionStubRepository()
        );
    }
}
