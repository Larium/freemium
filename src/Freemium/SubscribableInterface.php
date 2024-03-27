<?php

declare(strict_types=1);

namespace Freemium;

interface SubscribableInterface
{
    public function setBillingKey(string $key);

    /**
     * The id for this user in the remote billing gateway.
     *
     * @var string|null
     */
    public function getBillingKey(): ?string;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getEmail(): string;
}
