<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use Freemium\Freemium;
use PHPUnit\Framework\TestCase;
use Freemium\Domain\Subscription;
use Freemium\Domain\FixturesHelper;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscriptionStubRepository;

class ChargeSubscriptionHandlerTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    protected function setUp(): void
    {
        $this->fixturesSetUp();
        $this->eventProvider = new EventProvider();
    }

    public function testSuccessChargeHandle()
    {
        $subscription = $this->subscriptions('testChargePaidSubscription');
        $command = new ChargeSubscription($subscription);

        $this->handleResult($command, Event\SubscriptionPaid::class);
    }

    public function testHandleExpiredSubscription()
    {
        $subscription = $this->subscriptions('testExpiration');
        $command = new ChargeSubscription($subscription);

        Freemium::setExpiredPlan($this->subscriptionPlans('free'));

        $this->handleResult($command, Event\SubscriptionExpired::class);
    }

    public function testHandleInGraceSubscription()
    {
        $subscription = $this->subscriptions('testInGraceSubscription');
        $command = new ChargeSubscription($subscription);

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
            new SubscriptionStubRepository(),
            Freemium::getGateway()
        );
    }
}
