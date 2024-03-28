<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard;

use Freemium\Subscribable;
use Freemium\FixturesHelper;
use PHPUnit\Framework\TestCase;
use Freemium\Event\EventProvider;
use AktiveMerchant\Billing\Exception;
use AktiveMerchant\Billing\CreditCard;
use Freemium\Repository\SubscribableStubRepository;

class StoreCreditCardHandlerTest extends TestCase
{
    use FixturesHelper;

    private $eventProvider;

    private $repository;

    protected function setUp(): void
    {
        $this->fixturesSetUp();
        $this->eventProvider = new EventProvider();
        $this->repository = new SubscribableStubRepository();
    }

    public function testSuccessHandle()
    {
        $command = new StoreCreditCard(
            $this->creditCards('bogus_card'),
            $this->users('bob')
        );

        $handler = $this->createHandler();

        $handler->handle($command);

        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);

        $this->assertInstanceOf(Event\CreditCardStored::class, $event);
        $this->assertInstanceOf(Subscribable::class, $event->getSubscribable());
        $this->assertInstanceOf(CreditCard::class, $event->getCreditCard());

        $storage = $this->repository->getStorage();
        $this->assertEquals(1, count($storage));

        $subscribable = reset($storage);
        $this->assertNotNull($subscribable->getBillingKey());
    }

    public function testFailedHandle()
    {
        $command = new StoreCreditCard(
            $this->creditCards('bogus_card_fail'),
            $this->users('bob')
        );

        $handler = $this->createHandler();

        try {
            $handler->handle($command);
        } catch (\RuntimeException $e) {
            // continue
        }

        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);
        $this->assertInstanceOf(Event\CreditCardFailed::class, $event);
        $this->assertInstanceOf(Subscribable::class, $event->getSubscribable());
        $this->assertInstanceOf(CreditCard::class, $event->getCreditCard());
        $this->assertInstanceOf(\RuntimeException::class, $event->getException());
    }

    public function testFailedHandleOnException()
    {
        $command = new StoreCreditCard(
            $this->creditCards('bogus_card_exception'),
            $this->users('bob')
        );

        $handler = $this->createHandler();

        try {
            $handler->handle($command);
        } catch (\Exception $e) {
            // continue
        }

        $events = $this->eventProvider->releaseEvents();

        $this->assertEquals(1, count($events));
        $event = reset($events);
        $this->assertInstanceOf(Event\CreditCardFailed::class, $event);
        $this->assertInstanceOf(Subscribable::class, $event->getSubscribable());
        $this->assertInstanceOf(CreditCard::class, $event->getCreditCard());
        $this->assertInstanceOf(Exception::class, $event->getException());
    }

    private function createHandler()
    {
        return new StoreCreditCardHandler(
            $this->eventProvider,
            $this->repository
        );
    }
}
