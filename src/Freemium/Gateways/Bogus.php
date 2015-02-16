<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Gateways;

use AktiveMerchant\Billing\Gateways\Bogus as BogusGateway;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Response;

class Bogus extends BogusGateway
{
    public function charge($money, $billing_key, $options = array())
    {
        if ($billing_key == 1) {
            return new Response(true, null);
        } else {
            return new Response(false, null);
        }
    }
}
