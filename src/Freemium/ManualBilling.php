<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

trait ManualBilling
{
    public function getInstallmentAmount(array $options = array())
    {
        return $this->rate($options);
    }

    /**
     * Charge current subscription
     *
     * @access public
     * @return void
     */
    public function charge()
    {
        $response = $this->gateway()->charge(
            $this->billing_key,
            $this->getInstallmentAmount()
        );

        $transaction = new Transaction();
        $transaction->setProperties([
            'billing_key' => $this->billing_key,
            'amount' => $this->rate(),
            'success' => $response->success()
        ]);

        $this->transactions[] = $transaction;
        $this->last_transaction_at = new DateTime('now');

        if ($transaction->getSuccess()) {
            $this->receivePayment($transaction);
        } elseif (!$this->isInGrace()) {
            $this->expireAfterGrace($transaction);
        }
    }
}
