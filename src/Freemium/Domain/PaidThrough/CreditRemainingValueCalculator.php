<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTime;
use Freemium\Domain\Money;

class CreditRemainingValueCalculator extends PaidThroughCalculator
{
    private $paidThrough;

    private $inTrial;

    private $expireOn;

    public function getState(): ?SubscriptionState
    {
        if (!$this->getSubscription()->isInTrial()
            && $this->getSubscription()->getOriginalPlan()
            && $this->getSubscription()->getOriginalPlan()->isPaid()
        ) {
            $this->calculateRemainingValueInDays();

            return new SubscriptionState(
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
        $amount = $this->getSubscription()->remainingAmount();
        $dailyRate = $this->getSubscription()->getDailyRate();

        $currency = $dailyRate->getCurrency();
        if ($dailyRate->equals(Money::zero($currency))) {
            $this->paidThrough = new DateTime('today');

            return;
        }
        $amountMinor = (float) $amount->getMinorAmount();
        $dailyMinor = (float) $dailyRate->getMinorAmount();
        $days = (int) ceil($amountMinor / $dailyMinor);
        $this->paidThrough->modify("$days days");
    }
}
