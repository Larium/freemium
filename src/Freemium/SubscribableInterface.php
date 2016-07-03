<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

interface SubscribableInterface
{
    public function getBillingKey();

    public function getFirstName();

    public function getLastName();

    public function getEmail();
}
