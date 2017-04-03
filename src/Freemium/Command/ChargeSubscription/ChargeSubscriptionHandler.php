<?php

declare(strict_types=1);

namespace Freemium\Command\ChargeSubscription;

use Freemium\Freemium;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Repository\SubscriptionRepositoryInterface;

class ChargeSubscriptionHandler extends AbstractCommandHandler
{
    private $repository;

    public function __construct(
        EventProvider $eventProvider,
        SubscriptionRepositoryInterface $repository
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
    }

    public function handle(ChargeSubscription $command) : void
    {
        $subscription = $command->getSubscription();

        $gateway = Freemium::getGateway();

        $response = $gateway->charge(
            $subscription->rate(),
            $subscription->getSubscribable()->getBillingKey()
        );

        $transaction = $subscription->createTransaction($response);

        if ($transaction->isSuccess()) {
            $subscription->receivePayment($transaction);
            $event = new Event\SubscriptionPaid($subscription);
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
