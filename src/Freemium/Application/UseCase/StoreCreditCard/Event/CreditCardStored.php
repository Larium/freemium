<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\StoreCreditCard\Event;

use Freemium\Domain\Subscribable;
use AktiveMerchant\Billing\CreditCard;
use Freemium\Application\Event\DomainEvent;

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
