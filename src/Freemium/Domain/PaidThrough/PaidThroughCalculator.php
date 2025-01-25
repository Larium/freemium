<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use Freemium\Domain\Subscription;

abstract class PaidThroughCalculator
{
    private $successor;

    private $subscription;

    abstract protected function getState(): ?SubscriptionState;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function setSuccessor(PaidThroughCalculator $calculator)
    {
        $this->successor = $calculator;
    }

    public function calculate(): ?SubscriptionState
    {
        $paidThrough = $this->getState();

        if ($paidThrough === null && $this->successor !== null) {
            $paidThrough = $this->successor->calculate();
        }

        return $paidThrough;
    }

    protected function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
