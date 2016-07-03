<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Command\Subscription;

use Freemium\FixturesHelper;
use Freemium\Command\CommandBus;
use Freemium\SubscriptionChangeInterface;

class NewSubscriptionCommandTest extends \PHPUnit_Framework_TestCase
{
    use FixturesHelper;

    public function testNewSubscriptionFreePlan()
    {
        $command = new NewSubscription();
        $command->subscriptionPlan = $this->subscriptionPlans('free');
        $command->subscribable = $this->users('bob');

        $subscription = $this->getCommandBus()->handle($command);

        $this->assertInstanceOf('Freemium\Subscription', $subscription);

        $this->assertEquals(
            SubscriptionChangeInterface::REASON_NEW,
            $subscription->getSubscriptionChanges()->last()->getReason()
        );
    }

    private function getCommandBus()
    {
        return new CommandBus();
    }
}
