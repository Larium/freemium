<?php

declare(strict_types=1);

namespace Freemium;

interface SubscriptionChangeInterface
{
    const REASON_NEW        = 1;

    const REASON_EXPIRE     = 2;

    const REASON_UPGRADE    = 3;

    const REASON_DOWNGRADE  = 4;

    const REASON_CANCEL     = 5;
}
