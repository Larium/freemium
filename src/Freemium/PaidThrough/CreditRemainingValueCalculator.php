<?php

declare(strict_types = 1);

namespace Freemium\PaidThrough;

use DateTime;

class CreditRemainingValueCalculator extends PaidThroughCalculator
{
    private $paidThrough;

    private $inTrial;

    private $expireOn;

    public function getPaidThrough(): ?PaidThrough
    {
        if (!$this->getSubscription()->isInTrial()
            && $this->getSubscription()->getOriginalPlan()
            && $this->getSubscription()->getOriginalPlan()->isPaid()
        ) {
            $this->calculateRemainingValueInDays();

            return new PaidThrough(
                $this->paidThrough,
                $this->inTrial,
                $this->expireOn
            );
        }

        return null;
    }

    private function calculateRemainingValueInDays(): void
    {
        $this->expireOn = null;
        $this->inTrial = false;
        $this->paidThrough = new DateTime('today');
        $amount = $this->getSubscription()
            ->remainingAmount($this->getSubscription()->getOriginalPlan());

        $days = ceil($amount / $this->getSubscription()->getDailyRate());
        $this->paidThrough->modify("$days days");
    }
}
