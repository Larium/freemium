<?php

namespace Freemium\Command\Subscription;

use Freemium\Subscription;
use Freemium\Command\AbstractCommandHandler;

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

        return $subscription;
    }
}
