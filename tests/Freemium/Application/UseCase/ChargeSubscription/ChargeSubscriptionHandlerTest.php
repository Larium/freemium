<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use PHPUnit\Framework\TestCase;
use Freemium\Domain\Subscription;
use Freemium\Domain\FixturesHelper;
use Freemium\Domain\Gateways\BogusGatewayFactory;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscriptionStubRepository;
use Freemium\Domain\Repository\TransactionStubRepository;
use Freemium\Domain\SubscriptionStatus;
use Freemium\Infrastructure\Service\CustomIdGenerator;

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

        $this->handleResult($command, Event\SubscriptionExpired::class);
    }

    public function testHandleInGraceSubscription()
    {
        $subscription = $this->subscriptions('testInGraceSubscription');
        $command = new ChargeSubscription($subscription);

        $this->handleResult($command, Event\SubscriptionGraced::class);
    }

    public function testIdempotentCharge_firstCallWithKey_chargesAndRaisesEvent(): void
    {
        $subscriptionRepository = new SubscriptionStubRepository();
        $transactionRepository = new TransactionStubRepository();
        $subscription = $this->subscriptions('testChargePaidSubscription');
        $command = new ChargeSubscription($subscription, 'idem-key-123');

        $this->createHandlerWithRepositories($subscriptionRepository, $transactionRepository)->handle($command);
        $events = $this->eventProvider->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(Event\SubscriptionPaid::class, $events[0]);
        $this->assertCount(1, $subscription->getTransactions());
    }

    public function testIdempotentCharge_secondCallWithSameKey_doesNotChargeAgain(): void
    {
        $subscriptionRepository = new SubscriptionStubRepository();
        $transactionRepository = new TransactionStubRepository();
        $subscription = $this->subscriptions('testChargePaidSubscription');
        $command = new ChargeSubscription($subscription, 'idem-key-456');
        $handler = $this->createHandlerWithRepositories($subscriptionRepository, $transactionRepository);

        $handler->handle($command);
        $this->eventProvider->releaseEvents();

        $handler->handle($command);
        $events = $this->eventProvider->releaseEvents();

        $this->assertCount(0, $events);
        $this->assertCount(1, $subscription->getTransactions());
    }

    private function createHandlerWithRepositories(
        SubscriptionStubRepository $subscriptionRepository,
        TransactionStubRepository $transactionRepository
    ): ChargeSubscriptionHandler {
        return new ChargeSubscriptionHandler(
            $this->eventProvider,
            $subscriptionRepository,
            new BogusGatewayFactory(),
            $transactionRepository,
            new CustomIdGenerator()
        );
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
            $this->assertSame(SubscriptionStatus::CANCELED, $subscription->getStatus());
        }
    }

    private function createHandler(): ChargeSubscriptionHandler
    {
        return $this->createHandlerWithRepositories(
            new SubscriptionStubRepository(),
            new TransactionStubRepository()
        );
    }
}
