<?php

declare(strict_types=1);

namespace Freemium\Domain\PaidThrough;

use DateTimeImmutable;
use Freemium\Domain\Money;
use Freemium\Domain\RateCalculator;
use Freemium\Domain\Subscription;

class CreditRemainingValueCalculator extends PaidThroughCalculator
{
    private ?DateTimeImmutable $paidThrough = null;

    private bool $inTrial = false;

    private ?DateTimeImmutable $expireOn = null;

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
        $this->paidThrough = new DateTimeImmutable('today');

        $currency = $dailyRate->getCurrency();
        if ($dailyRate->equals(Money::zero($currency))) {
            return;
        }

        $amount = $subscription->remainingAmount();
        $amountMinor = (float) $amount->getMinorAmount();
        $dailyMinor = (float) $dailyRate->getMinorAmount();
        $days = (int) ceil($amountMinor / $dailyMinor);
        $this->paidThrough = $this->paidThrough->modify("$days days");
    }
}
