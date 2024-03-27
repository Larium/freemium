<?php

declare(strict_types=1);

namespace Freemium;

use Freemium\SubscribableInterface;

class User implements SubscribableInterface
{
    private $billingKey;

    private $firstName;

    private $lastName;

    private $email;

    public function __construct(string $email, string $firstName, string $lastName)
    {
        $this->email = $email;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
    }

    public function setBillingKey(string $key)
    {
        $this->billingKey = $key;
    }

    public function getBillingKey(): ?string
    {
        return $this->billingKey;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
