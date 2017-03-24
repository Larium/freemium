<?php

declare(strict_types=1);

namespace Freemium\Event\Subscribable;

use Freemium\Event\DomainEvent;
use Freemium\SubscribableInterface;
use AktiveMerchant\Billing\CreditCard;

class CreditCardStored extends DomainEvent
{
    const NAME = 'creditcard.stored';

    private $creditCard;

    private $subscribable;

    public function __construct(
        CreditCard $creditCard,
        SubscribableInterface $subscribable
    ) {
        $this->creditCard = $creditCard;
        $this->subscribable = $subscribable;
    }

    public function getSubscribable() : SubscribableInterface
    {
        return $this->subscribable;
    }

    public function getCreditCard() : CreditCard
    {
        return $this->creditCard;
    }
}
