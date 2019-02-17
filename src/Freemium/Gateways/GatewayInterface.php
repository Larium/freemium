<?php

namespace Freemium\Gateways;

use AktiveMerchant\Billing\Response;
use AktiveMerchant\Billing\CreditCard;

interface GatewayInterface
{
    /**
     * Charge a credit card through a stored reference.
     *
     * @param int $money
     * @param string $billing_key
     * @param array $options
     *
     * @return Response
     */
    public function charge(int $money, string $billing_key, array $options = array()): Response;

    /**
     * Stores a reference of a credit card.
     *
     * @param CreditCard $creditcard
     * @param array      $options
     *
     * @return Response
     */
    public function store(CreditCard $creditcard, array $options = array()): Response;
}
