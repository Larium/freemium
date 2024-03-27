<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

use Freemium\Subscription;

abstract class PaidThroughCalculator
{
    private $successor;

    private $subscription;

    abstract protected function getPaidThrough(): ?PaidThrough;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function setSuccessor(PaidThroughCalculator $calculator)
    {
        $this->successor = $calculator;
    }

    public function calculate(): ?PaidThrough
    {
        $paidThrough = $this->getPaidThrough();

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
