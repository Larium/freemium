<?php

declare(strict_types=1);

namespace Freemium;

interface Subscribable
{
    /**
     * The id in the remote billing gateway.
     */
    public function getBillingKey(): ?string;

    public function updateBillingKey(string $billingKey): void;

    /**
     * The id in the local system.
     */
    public function getCustomerId(): string;
}
