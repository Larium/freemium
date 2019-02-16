<?php

declare(strict_types = 1);

namespace Freemium\Command\ChargeSubscription;

use Freemium\Freemium;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Gateways\GatewayInterface;
use Freemium\Repository\SubscriptionRepositoryInterface;

class ChargeSubscriptionHandler extends AbstractCommandHandler
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    private $repository;

    /**
     * @var GatewayInterface
     */
    private $gateway;

    public function __construct(
        EventProvider $eventProvider,
        SubscriptionRepositoryInterface $repository,
        GatewayInterface $gateway
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
        $this->gateway = $gateway;
    }

    public function handle(ChargeSubscription $command) : void
    {
        $subscription = $command->getSubscription();

        $response = $this->gateway->charge(
            $subscription->rate(),
            $subscription->getSubscribable()->getBillingKey()
        );

        $transaction = $subscription->createTransaction($response);

        $event = new Event\SubscriptionPaid($subscription);
        if ($transaction->isSuccess()) {
            $subscription->receivePayment($transaction);
        } elseif ($subscription->isExpired()) {
            $subscription->expireNow();
            $event = new Event\SubscriptionExpired($subscription);
        } elseif (!$subscription->isInGrace()) {
            $subscription->expireAfterGrace();
            $event = new Event\SubscriptionGraced($subscription);
        }

        $this->repository->update($subscription);

        $this->getEventProvider()->raise($event);
    }
}
