<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\ChargeSubscription;

use Freemium\Domain\Subscription;
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
        GatewayInterface $gateway
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
        $this->gateway = $gateway;
    }

    public function handle(ChargeSubscription $command): void
    {
        $subscription = $command->getSubscription();

        $response = $this->gateway->charge(
            $subscription->rate(),
            $subscription->getSubscribable()->getBillingKey()
        );

        $transaction = $subscription->createTransaction($response);

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
