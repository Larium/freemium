<?php

namespace Freemium\Gateways;

use AktiveMerchant\Billing\Response;
use AktiveMerchant\Billing\Exception;
use AktiveMerchant\Billing\CreditCard;
use AktiveMerchant\Billing\Gateways\Bogus as BogusGateway;

class Bogus implements GatewayInterface
{
    public function charge(int $money, string $billing_key, array $options = []): Response
    {
        if ($billing_key == '1') {
            return new Response(true, 'SUCCESS');
        }

        return new Response(false, 'FAILED');
    }

    public function store(CreditCard $creditcard, array $options = []): Response
    {
        switch ($creditcard->number) {
            case '1':
                return new Response(
                    true,
                    'SUCCESS',
                    ['billingid' => '1'],
                    [
                        'test' => true,
                        'authorization' => '1'
                    ]
                );
            case '2':
                throw new Exception("Http Client exception");
            default:
                return new Response(
                    false,
                    'FAILURE',
                    [
                        'billingid' => null,
                        'error' => "Error"
                    ],
                    ['test' => true]
                );
        }
    }
}
