<?php

namespace Freemium\Gateways;

use AktiveMerchant\Billing\Exception;
use AktiveMerchant\Billing\Gateways\Bogus as BogusGateway;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Response;

class Bogus implements GatewayInterface
{
    public function charge(int $money, string $billing_key, array $options = array()): Response
    {
        if ($billing_key == '1') {
            return new Response(true, 'SUCCESS');
        }

        return new Response(false, 'FAILED');
    }

    public function store(CreditCard $creditcard, array $options = array()): Response
    {
        switch ($creditcard->number) {
            case '1':
                return new Response(
                    true,
                    'SUCCESS',
                    array('billingid' => '1'),
                    array(
                        'test' => true,
                        'authorization' => '1'
                    )
                );
            case '2':
                throw new Exception("Http Client exception");
            default:
                return new Response(
                    false,
                    'FAILURE',
                    array(
                        'billingid' => null,
                        'error' => "Error"
                    ),
                    array('test' => true)
                );
        }
    }
}
