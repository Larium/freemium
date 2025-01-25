<?php

declare(strict_types=1);

namespace Freemium;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';

    case PAST_DUE = 'past_due';

    case CANCELED = 'canceled';
}
