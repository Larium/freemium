<?php

namespace Freemium\Command\Subscription;

use Freemium\Assertion;
use Freemium\Subscription;

class NewSubscriptionHandler
{
    use Assertion;

    public function handle(NewSubscription $command)
    {
        $this->validate($command);

        return $this->createSubscription($command);
    }

    private function validate($command)
    {
        $this->assertInstanceOf(
            'Freemium\SubscribableInterface',
            $command->subscribable
        );

        $this->assertInstanceOf(
            'Freemium\SubscriptionPlanInterface',
            $command->subscriptionPlan
        );
    }

    private function createSubscription($command)
    {
        $subscription = new Subscription($command->subscribable);
        $subscription->setSubscriptionPlan($command->subscriptionPlan);

        return $subscription;
    }
}
