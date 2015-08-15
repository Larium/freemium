<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use DateTime;

class Transaction extends \Larium\AbstractModel
{
    /**
     * Whether transaction was success or not.
     *
     * @var boolean
     */
    protected $success;

    /**
     * Credit card token used for this transaction.
     *
     * @var string
     */
    protected $billing_key;

    /**
     * Amount paid for this transaction.
     *
     * @var integer
     */
    protected $amount;

    /**
     * Generic message that describes current transaction.
     *
     * @var string
     */
    protected $message;

    /**
     * When transaction created?
     *
     * @var DateTime
     */
    protected $created_at;

    /**
     * The subscription that created this transaction.
     *
     * @var Subscription
     */
    protected $subscription;

    public function __construct()
    {
        $this->created_at = new DateTime();
    }
}
