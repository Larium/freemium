<?php

namespace Freemium;

interface SubscribableInterface
{
    public function getBillingKey();

    public function getFirstName();

    public function getLastName();

    public function getEmail();
}
