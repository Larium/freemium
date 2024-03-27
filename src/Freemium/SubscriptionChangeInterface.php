<?php

declare(strict_types=1);

namespace Freemium;

interface SubscriptionChangeInterface
{
    public const REASON_NEW        = 1;

    public const REASON_EXPIRE     = 2;

    public const REASON_UPGRADE    = 3;

    public const REASON_DOWNGRADE  = 4;

    public const REASON_CANCEL     = 5;
}
