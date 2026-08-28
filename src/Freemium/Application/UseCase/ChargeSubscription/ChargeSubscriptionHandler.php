<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use RuntimeException;
use Freemium\Domain\Clock;
use Freemium\Domain\IdGenerator;
use Freemium\Domain\SystemClock;
use Freemium\Domain\Transaction;
use Freemium\Domain\Subscription;
use Freemium\Application\Event\DomainEvent;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Gateways\GatewayFactory;
use Freemium\Domain\Repository\CouponRedemptionRepository;
use Freemium\Domain\Repository\TransactionRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;
use Freemium\Application\UseCase\ChargeSubscription\Event\SubscriptionPayFailed;

class ChargeSubscriptionHandler extends AbstractCommandHandler
{
    public function __construct(
        EventProvider $eventProvider,
        private readonly SubscriptionRepository $repository,
        private readonly CouponRedemptionRepository $couponRedemptionRepository,
        private readonly GatewayFactory $gatewayFactory,
        private readonly TransactionRepository $transactionRepository,
        private readonly IdGenerator $idGenerator,
        private readonly Clock $clock = new SystemClock(),
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
            throw new RuntimeException(
                'Customer does not have a billing key setup. Subscription: ' . $subscription->getToken() . ', customer: ' . $subscription->getSubscribable()->getCustomerId() . '.'
            );
        }

        $on = $this->clock->now()->setTime(0, 0, 0);
        $bestRedemption = $this->couponRedemptionRepository->findBestActiveForSubscription($subscription, $on);
        $activeCoupon = $bestRedemption?->getCoupon();

        $transactionToken = $this->idGenerator->generate(Transaction::TOKEN_PREFIX);
        $transaction = $subscription->createTransaction($transactionToken, $on, $idempotencyKey, $activeCoupon);

        $this->transactionRepository->insert($transaction);
        $gateway = $this->gatewayFactory->getGatewayFor($subscription->getSubscribable());
        $response = $gateway->charge(
            $subscription->billingAmount($activeCoupon),
            $subscription->getSubscribable()->getBillingKey()
        );

        $transaction->capture($response);

        if ($transaction->isSuccess()) {
            $subscription->receivePayment($on);
            $event = new Event\SubscriptionPaid($subscription);

            $this->finalize($subscription, $transaction, $event);
            return;
        }

        if ($subscription->isCancellationDue($on)) {
            $subscription->cancel($on);
            $event = new Event\SubscriptionExpired($subscription);

            $this->finalize($subscription, $transaction, $event);
            return;
        }

        if (!$subscription->isInGrace($on)) {
            $subscription->markPastDue($on);
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
