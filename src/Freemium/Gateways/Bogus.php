<?php

namespace Freemium\Gateways;

use AktiveMerchant\Billing\Gateways\Bogus as BogusGateway;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Response;

class Bogus extends BogusGateway implements GatewayInterface
{
    public function charge($money, $billing_key, $options = array())
    {
        if ($billing_key == '1') {
            return new Response(true, null);
        }

        return new Response(false, null);
    }
}
