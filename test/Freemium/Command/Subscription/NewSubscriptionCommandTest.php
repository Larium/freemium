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
        $command = new NewSubscription(
            $this->users('bob'),
            $this->subscriptionPlans('free')
        );

        $subscription = $this->getCommandBus()->handle($command);

        $this->assertInstanceOf('Freemium\Subscription', $subscription);

        $changes = $subscription->getSubscriptionChanges();
        $this->assertEquals(
            SubscriptionChangeInterface::REASON_NEW,
            end($changes)->getReason()
        );

        $this->assertNull($subscription->getPaidThrough());
    }

    public function testNewSubscriptionPaidPlan()
    {
        $command = new NewSubscription(
            $this->users('sally'),
            $this->subscriptionPlans('basic')
        );

        $subscription = $this->getCommandBus()->handle($command);

        $this->assertInstanceOf('Freemium\Subscription', $subscription);

        $changes = $subscription->getSubscriptionChanges();
        $this->assertEquals(
            SubscriptionChangeInterface::REASON_NEW,
            end($changes)->getReason()
        );

        $this->assertNotNull($subscription->getPaidThrough());
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionMessage Can not create paid subscription without a credit card.
     */
    public function testNewSubscriptionPaidPlanWithoutBillingKey()
    {
        $command = new NewSubscription(
            $this->users('sue'),
            $this->subscriptionPlans('basic')
        );

        $subscription = $this->getCommandBus()->handle($command);
    }

    private function getCommandBus()
    {
        return new CommandBus();
    }
}
