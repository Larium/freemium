<?php

declare(strict_types=1);

namespace Freemium;

use Freemium\Subscribable;

class User implements Subscribable
{
    private ?string $billingKey = null;

    public function __construct(
        private readonly string $customerId,
        ?string $billingKey = null
    ) {
        $this->billingKey = $billingKey;
    }

    public function updateBillingKey(string $billingKey): void
    {
        $this->billingKey = $billingKey;
    }

    public function getBillingKey(): ?string
    {
        return $this->billingKey;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
