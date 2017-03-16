<?php

namespace Freemium\Command\CreateSubscription;

use Freemium\Subscription;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Event\Subscription\SubscriptionCreated;

class NewSubscriptionHandler extends AbstractCommandHandler
{
    public function handle(NewSubscription $command)
    {
        return $this->createSubscription($command);
    }

    private function createSubscription($command)
    {
        $subscription = new Subscription(
            $command->subscribable,
            $command->subscriptionPlan
        );

        $this->getEventProvider()->raise(new SubscriptionCreated($subscription));

        return $subscription;
    }
}
