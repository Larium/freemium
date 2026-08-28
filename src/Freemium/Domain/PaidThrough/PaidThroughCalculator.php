<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTimeImmutable;
use Freemium\Domain\Subscription;

abstract class PaidThroughCalculator
{
    private ?PaidThroughCalculator $successor = null;

    public function __construct(
        private readonly Subscription $subscription,
        private readonly DateTimeImmutable $on,
    ) {
    }

    abstract protected function getState(): ?SubscriptionState;

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
            $state = (new DefaultCalculator($this->subscription, $this->on))->calculate();
        }

        return $state;
    }

    protected function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    protected function getOn(): DateTimeImmutable
    {
        return $this->on;
    }
}
