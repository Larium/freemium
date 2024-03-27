<?php

declare(strict_types=1);

namespace Freemium\PaidThrough;

use DateTime;
use Freemium\Freemium;

class NewPaidSubscriptionCalculator extends PaidThroughCalculator
{
    protected function getPaidThrough(): ?PaidThrough
    {
        if ($this->getSubscription()->getOriginalPlan() === null) {
            return new PaidThrough(
                (new DateTime('today'))->modify(Freemium::$daysFreeTrial . ' days'),
                true,
                null
            );
        }

        return null;
    }
}
