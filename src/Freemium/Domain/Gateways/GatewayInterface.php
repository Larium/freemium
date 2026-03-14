<?php

namespace Freemium\Domain\Gateways;

use Freemium\Domain\Money;
use AktiveMerchant\Billing\Response;
use AktiveMerchant\Billing\CreditCard;

interface GatewayInterface
{
    /**
     * Charge a credit card through a stored reference.
     *
     * @param Money $money Amount in minor units (e.g. cents)
     * @param string $billing_key
     * @param array $options
     *
     * @return Response
     */
    public function charge(Money $money, string $billing_key, array $options = []): Response;

    /**
     * Stores a reference of a credit card.
     *
     * @param CreditCard $creditcard
     * @param array      $options
     *
     * @return Response
     */
    public function store(CreditCard $creditcard, array $options = []): Response;
}
