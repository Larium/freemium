<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard\Event;

use Freemium\Subscribable;
use Freemium\Event\DomainEvent;
use AktiveMerchant\Billing\CreditCard;

class CreditCardStored extends DomainEvent
{
    public const NAME = 'creditcard.stored';

    private $creditCard;

    private $subscribable;

    public function __construct(
        CreditCard $creditCard,
        Subscribable $subscribable
    ) {
        $this->creditCard = $creditCard;
        $this->subscribable = $subscribable;
    }

    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }

    public function getCreditCard(): CreditCard
    {
        return $this->creditCard;
    }
}
