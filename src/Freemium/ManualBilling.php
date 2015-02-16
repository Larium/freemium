<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class ManualBilling
{
    /**
     * The Subscription to charge
     *
     * @var mixed
     * @access protected
     */
    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getInstallmentAmount(array $options = array())
    {
        return $this->subscription->rate($options);
    }

    /**
     * Charge current subscription
     *
     * @access public
     * @return Transaction
     */
    public function charge()
    {
        $response = $this->subscription->gateway()->charge(
            $this->subscription->rate(),
            $this->subscription->getBillingKey(),
            $this->getInstallmentAmount()
        );

        $transaction = new Transaction();
        $transaction->setProperties([
            'billing_key' => $this->subscription->getBillingKey(),
            'amount' => $this->subscription->rate(),
            'success' => $response->success()
        ]);

        $this->transactions[] = $transaction;
        $this->subscription->setLastTransactionAt(new DateTime('now'));

        if ($transaction->getSuccess()) {
            $this->subscription->receivePayment($transaction);
        } elseif (!$this->subscription->isInGrace()) {
            $this->subscription->expireAfterGrace($transaction);
        }

        return $transaction;
    }
}
