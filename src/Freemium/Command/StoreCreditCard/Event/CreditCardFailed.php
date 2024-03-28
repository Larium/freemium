<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard\Event;

use Throwable;
use Freemium\Subscribable;
use Freemium\Event\DomainEvent;
use AktiveMerchant\Billing\CreditCard;

class CreditCardFailed extends DomainEvent
{
    public const NAME = 'creditcard.failed';

    private $creditCard;

    private $subscribable;

    private $exception;

    public function __construct(
        CreditCard $creditCard,
        Subscribable $subscribable,
        Throwable $exception
    ) {
        $this->creditCard = $creditCard;
        $this->subscribable = $subscribable;
        $this->exception = $exception;
    }

    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }

    public function getCreditCard(): CreditCard
    {
        return $this->creditCard;
    }

    public function getException()
    {
        return $this->exception;
    }
}
