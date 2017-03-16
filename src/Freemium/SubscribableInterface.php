<?php

declare(strict_types=1);

namespace Freemium;

interface SubscribableInterface
{
    public function getBillingKey() : ?string;

    public function getFirstName() : string;

    public function getLastName() : string;

    public function getEmail() : string;
}
