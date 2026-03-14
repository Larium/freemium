<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use RuntimeException;
use Freemium\Domain\IdGenerator;
use Freemium\Domain\Transaction;
use Freemium\Domain\Subscription;
use Freemium\Application\Event\DomainEvent;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Gateways\GatewayInterface;
use Freemium\Domain\Repository\TransactionRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;
use Freemium\Application\UseCase\ChargeSubscription\Event\SubscriptionPayFailed;

class ChargeSubscriptionHandler extends AbstractCommandHandler
{
    public function __construct(
        EventProvider $eventProvider,
        private readonly SubscriptionRepository $repository,
        private readonly GatewayInterface $gateway,
        private readonly TransactionRepository $transactionRepository,
        private readonly IdGenerator $idGenerator
    ) {
        parent::__construct($eventProvider);
    }

    public function handle(ChargeSubscription $command): void
    {
        $subscription = $command->getSubscription();
        $idempotencyKey = $command->getIdempotencyKey();

        if ($idempotencyKey !== null) {
            $existing = $this->transactionRepository->findByIdempotencyKey($idempotencyKey);
            if ($existing !== null) {
                return;
            }
        }

        if ($subscription->getSubscribable()->getBillingKey() === null) {
            throw new RuntimeException('Customer does not have a billing key setup');
        }

        // 1. Create pending transaction first (audit trail before gateway call)
        $transactionToken = $this->idGenerator->generate(Transaction::TOKEN_PREFIX);
        $transaction = $subscription->createTransaction($transactionToken, $idempotencyKey);

        $this->transactionRepository->insert($transaction);
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

            $this->finalize($subscription, $transaction, $event);
            return;
        }

        if ($subscription->isExpired()) {
            $subscription->expireNow();
            $event = new Event\SubscriptionExpired($subscription);

            $this->finalize($subscription, $transaction, $event);
            return;
        }

        if (!$subscription->isInGrace()) {
            $subscription->expireAfterGrace();
            $event = new Event\SubscriptionGraced($subscription);

            $this->finalize($subscription, $transaction, $event);
            return;
        }

        $event = new SubscriptionPayFailed($subscription);
        $this->finalize($subscription, $transaction, $event);
    }

    private function finalize(Subscription $subscription, Transaction $transaction, DomainEvent $event): void
    {
        $this->repository->update($subscription);
        $this->transactionRepository->update($transaction);
        $this->getEventProvider()->raise($event);
    }
}
