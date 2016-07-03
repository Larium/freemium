<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Command\Subscription;

use Freemium\Assertion;
use Freemium\Subscription;
use Freemium\Command\Handler;

class NewSubscriptionHandler implements Handler
{
    use Assertion;

    public function handle($command)
    {
        $this->validate($command);

        return $this->createSubscription($command);
    }

    private function validate($command)
    {
        $this->assertInstanceOf(
            'Freemium\Command\Subscription\NewSubscription',
            $command
        );

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
        $subscription = new Subscription();
        $subscription->setSubscriptionPlan($command->subscriptionPlan);

        return $subscription;
    }
}
