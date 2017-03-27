<?php

namespace Freemium\Gateways;

use AktiveMerchant\Billing\CreditCard;

interface GatewayInterface
{
    /**
     * Charge a credit card through a stored reference.
     *
     * @param mixed $money
     * @param string $billing_key
     * @param array $options
     *
     * @return \AktiveMerchant\Billing\Response
     */
    public function charge($money, $billing_key, $options = array());

    /**
     * Stores a reference of a credit card.
     *
     * @param CreditCard $creditcard
     * @param array      $options
     *
     * @return \AktiveMerchant\Billing\Response
     */
    public function store(CreditCard $creditcard, $options = array());

    /**
     * Unstores a reference of a credit card.
     *
     * @param mixed $reference
     * @param array $options
     *
     * @return \AktiveMerchant\Billing\Response
     */
    public function unstore($reference, $options = array());
}
