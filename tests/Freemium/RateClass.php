<?php

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
        SubscriptionPlanInterface $plan = null
    ) {
        return $this->rate;
    }
}
