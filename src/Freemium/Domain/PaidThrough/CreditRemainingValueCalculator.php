<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTime;
use Freemium\Domain\Money;
use Freemium\Domain\RateCalculator;
use Freemium\Domain\Subscription;

class CreditRemainingValueCalculator extends PaidThroughCalculator
{
    private $paidThrough;

    private $inTrial;

    private $expireOn;

    private RateCalculator $rateCalculator;

    public function __construct(Subscription $subscription, RateCalculator $rateCalculator)
    {
        parent::__construct($subscription);
        $this->rateCalculator = $rateCalculator;
    }

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
        $subscription = $this->getSubscription();
        $plan = $subscription->getSubscriptionPlan();
        $dailyRate = $this->rateCalculator->dailyRate($plan);

        $this->expireOn = null;
        $this->inTrial = false;
        $this->paidThrough = new DateTime('today');

        $currency = $dailyRate->getCurrency();
        if ($dailyRate->equals(Money::zero($currency))) {
            return;
        }

        $amount = $subscription->remainingAmount();
        $amountMinor = (float) $amount->getMinorAmount();
        $dailyMinor = (float) $dailyRate->getMinorAmount();
        $days = (int) ceil($amountMinor / $dailyMinor);
        $this->paidThrough->modify("$days days");
    }
}
