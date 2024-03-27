<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard;

use Freemium\SubscribableInterface;
use AktiveMerchant\Billing\CreditCard;

class StoreCreditCard
{
    private $creditCard;

    private $subscribable;

    public function __construct(
        CreditCard $creditCard,
        SubscribableInterface $subscribable
    ) {
        $this->creditCard = $creditCard;
        $this->subscribable = $subscribable;
    }

    public function getCreditCard(): CreditCard
    {
        return $this->creditCard;
    }

    public function getSubscribable(): SubscribableInterface
    {
        return $this->subscribable;
    }
}
