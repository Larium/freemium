<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use RuntimeException;
use Freemium\Domain\IdGenerator;
use Freemium\Domain\Subscription;
use Freemium\Domain\Transaction;
use Freemium\Application\Event\DomainEvent;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Gateways\GatewayInterface;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;
use Freemium\Application\UseCase\ChargeSubscription\Event\SubscriptionPayFailed;

class ChargeSubscriptionHandler extends AbstractCommandHandler
{
    /**
     * @var SubscriptionRepository
     */
    private $repository;

    /**
     * @var GatewayInterface
     */
    private $gateway;

    public function __construct(
        EventProvider $eventProvider,
        SubscriptionRepository $repository,
        GatewayInterface $gateway,
        private readonly IdGenerator $idGenerator
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
        $this->gateway = $gateway;
    }

    public function handle(ChargeSubscription $command): void
    {
        $subscription = $command->getSubscription();

        if ($subscription->getSubscribable()->getBillingKey() === null) {
            throw new RuntimeException('Customer does not have a billing key setup');
        }

        // 1. Create pending transaction first (audit trail before gateway call)
        $transactionToken = $this->idGenerator->generate(Transaction::TOKEN_PREFIX);
        $transaction = $subscription->createTransaction($transactionToken);

        // 2. Call gateway (external, irreversible)
        $response = $this->gateway->charge(
            $subscription->billingAmount(),
            $subscription->getSubscribable()->getBillingKey()
        );

        // 3. Capture gateway result into the pending transaction
        $subscription->captureTransaction($response);

        if ($transaction->isSuccess()) {
            $subscription->receivePayment();
            $event = new Event\SubscriptionPaid($subscription);

            $this->finalize($subscription, $event);
            return;
        }

        if ($subscription->isExpired()) {
            $subscription->expireNow();
            $event = new Event\SubscriptionExpired($subscription);

            $this->finalize($subscription, $event);
            return;
        }

        if (!$subscription->isInGrace()) {
            $subscription->expireAfterGrace();
            $event = new Event\SubscriptionGraced($subscription);

            $this->finalize($subscription, $event);
            return;
        }

        $event = new SubscriptionPayFailed($subscription);
        $this->finalize($subscription, $event);
    }

    private function finalize(Subscription $subscription, DomainEvent $event): void
    {
        $this->repository->update($subscription);
        $this->getEventProvider()->raise($event);
    }
}
