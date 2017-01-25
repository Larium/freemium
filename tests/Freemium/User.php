<?php

namespace Freemium;

use Freemium\SubscribableInterface;

class User implements SubscribableInterface
{
    private $billingKey;

    private $firstName;

    private $lastName;

    private $email;

    public function __construct($email, $firstName, $lastName)
    {
        $this->email = $email;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
    }

    public function getBillingKey()
    {
        return $this->billingKey;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
