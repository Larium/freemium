<?php

declare(strict_types=1);

namespace Freemium\Command\ChargeSubscription;

use Freemium\Subscription;

class ChargeSubscription
{
    private $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }
}
