<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Model;

class User extends Subscribable
{
    protected $firstName;

    protected $lastName;

    protected $email;

    protected $billingKey;

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

    public function getBillingKey()
    {
        return $this->billingKey;
    }
}
