<?php

declare(strict_types=1);

namespace Freemium\Domain;

use Larium\StateMachine\State;
use Larium\StateMachine\StateMachine;
use Larium\StateMachine\Transition;

final class SubscriptionStateMachine
{
    public const TRANSITION_PAY = 'pay';

    public const TRANSITION_PAST_DUE = 'past_due';

    public const TRANSITION_CANCEL = 'cancel';

    public static function create(Subscription $subscription): StateMachine
    {
        $sm = new StateMachine($subscription);
        $sm->addState(new State('active', State::TYPE_INITIAL));
        $sm->addState(new State('past_due'));
        $sm->addState(new State('canceled', State::TYPE_FINAL));
        $sm->addTransition(new Transition(self::TRANSITION_PAY, ['active', 'past_due'], 'active'));
        $sm->addTransition(new Transition(self::TRANSITION_PAST_DUE, ['active'], 'past_due'));
        $sm->addTransition(new Transition(self::TRANSITION_CANCEL, ['active', 'past_due'], 'canceled'));
        $sm->initialize();

        return $sm;
    }
}
