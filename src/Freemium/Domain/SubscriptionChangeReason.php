<?php

declare(strict_types=1);

namespace Freemium\Domain;

enum SubscriptionChangeReason: int
{
    case REASON_NEW        = 1;

    case REASON_EXPIRE     = 2;

    case REASON_UPGRADE    = 3;

    case REASON_DOWNGRADE  = 4;

    case REASON_CANCEL     = 5;
}
