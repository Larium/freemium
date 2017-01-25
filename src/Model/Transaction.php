<?php

namespace Model;

use Freemium\Transaction as FreemiumTransaction;

class Transaction
{
    protected $id;

    protected $success;

    protected $billing_key;

    protected $amount;

    protected $message;

    protected $created_at;

    protected $subscription;

    public function __construct()
    {
        $this->created_at = new DateTime();
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
