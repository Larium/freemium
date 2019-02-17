<?php

declare(strict_types = 1);

namespace Freemium;

use DateTime;

class RateClass implements RateInterface
{
    use Rate;

    public function __construct($rate = null)
    {
        $this->rate = null === $rate ? 1000 : $rate; # 10 dollars
    }

    public function rate(
        DateTime $date = null,
        SubscriptionPlan $plan = null
    ) : int {
        return $this->rate;
    }
}
