<?php

declare(strict_types=1);

namespace Freemium\Domain;

use Larium\StateMachine\Exception\StateException;
use PHPUnit\Framework\TestCase;

class SubscriptionStateMachineTest extends TestCase
{
    use FixturesHelper;

    public function testInitialStateIsActive(): void
    {
        $sub = $this->buildSubscription();
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertSame('active', $sm->getCurrentState()->getName());
        $this->assertSame(SubscriptionStatus::ACTIVE, $sub->getStatus());
    }

    public function testPayTransitionFromActive(): void
    {
        $sub = $this->buildSubscription(['subscription_plan' => $this->subscriptionPlans('basic')]);
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertTrue($sm->can(SubscriptionStateMachine::TRANSITION_PAY));
        $sm->apply(SubscriptionStateMachine::TRANSITION_PAY);
        $this->assertSame('active', $sm->getCurrentState()->getName());
    }

    public function testMarkPastDueFromActive(): void
    {
        $sub = $this->buildSubscription(['subscription_plan' => $this->subscriptionPlans('basic')]);
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertTrue($sm->can(SubscriptionStateMachine::TRANSITION_PAST_DUE));
        $sm->apply(SubscriptionStateMachine::TRANSITION_PAST_DUE);
        $this->assertSame('past_due', $sm->getCurrentState()->getName());
        $this->assertSame(SubscriptionStatus::PAST_DUE, $sub->getStatus());
    }

    public function testCancelFromActive(): void
    {
        $sub = $this->buildSubscription();
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertTrue($sm->can(SubscriptionStateMachine::TRANSITION_CANCEL));
        $sm->apply(SubscriptionStateMachine::TRANSITION_CANCEL);
        $this->assertSame('canceled', $sm->getCurrentState()->getName());
        $this->assertSame(SubscriptionStatus::CANCELED, $sub->getStatus());
    }

    public function testPayFromPastDueReturnsToActive(): void
    {
        $sub = $this->subscriptions('testInGraceSubscription');
        $sub->markPastDue();
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertSame('past_due', $sm->getCurrentState()->getName());
        $this->assertTrue($sm->can(SubscriptionStateMachine::TRANSITION_PAY));
        $sm->apply(SubscriptionStateMachine::TRANSITION_PAY);
        $this->assertSame('active', $sm->getCurrentState()->getName());
    }

    public function testCancelFromPastDue(): void
    {
        $sub = $this->subscriptions('testInGraceSubscription');
        $sub->markPastDue();
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertTrue($sm->can(SubscriptionStateMachine::TRANSITION_CANCEL));
        $sm->apply(SubscriptionStateMachine::TRANSITION_CANCEL);
        $this->assertSame('canceled', $sm->getCurrentState()->getName());
    }

    public function testInvalidTransitionFromCanceledThrows(): void
    {
        $sub = $this->buildSubscription();
        $sub->cancel();
        $sm = SubscriptionStateMachine::create($sub);

        $this->assertSame('canceled', $sm->getCurrentState()->getName());
        $this->assertFalse($sm->can(SubscriptionStateMachine::TRANSITION_PAY));

        $this->expectException(StateException::class);
        $sm->apply(SubscriptionStateMachine::TRANSITION_PAY);
    }
}
