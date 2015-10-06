<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

interface SubscriptionChangeInterface
{
    const REASON_NEW        = 1;

    const REASON_EXPIRE     = 2;

    const REASON_UPGRADE    = 3;

    const REASON_DOWNGRADE  = 4;
}
