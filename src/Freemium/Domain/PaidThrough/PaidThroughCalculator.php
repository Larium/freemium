<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use Freemium\Domain\Subscription;

abstract class PaidThroughCalculator
{
    private ?PaidThroughCalculator $successor = null;

    private Subscription $subscription;

    abstract protected function getState(): ?SubscriptionState;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function setSuccessor(PaidThroughCalculator $calculator): void
    {
        $this->successor = $calculator;
    }

    public function calculate(): SubscriptionState
    {
        $state = $this->getState();

        if ($state === null && $this->successor !== null) {
            $state = $this->successor->calculate();
        }
        if ($state === null && $this->successor === null) {
            $state = (new DefaultCalculator($this->subscription))->calculate();
        }

        return $state;
    }

    protected function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
