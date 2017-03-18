<?php

declare(strict_types=1);

namespace Freemium\Command\ChargeSubscription;

use Freemium\Freemium;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Event\Subscription\SubscriptionPaid;
use Freemium\Event\Subscription\SubscriptionGraced;
use Freemium\Event\Subscription\SubscriptionExpired;
use Freemium\Repository\SubscriptionRepositoryInterface;

class ChargeSubscriptionHandler extends AbstractCommandHandler
{
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
            $subscription->getBillingKey()
        );

        $transaction = $subscription->createTransaction($response);

        if ($transaction->isSuccess()) {
            $subscription->receivePayment($transaction);
            $event = new SubscriptionPaid($subscription);
        } elseif ($subscription->isExpired()) {
            $subscription->expireNow();
            $event = new SubscriptionExpired($subscription);
        } elseif (!$subscription->isInGrace()) {
            $subscription->expireAfterGrace($transaction);
            $event = new SubscriptionGraced($subscription);
        }

        $this->repository->update($subscription);

        $this->getEventProvider()->raise($event);
    }
}
