<?php

declare(strict_types=1);

namespace Freemium\Event\Subscribable;

use Throwable;
use Freemium\Event\DomainEvent;
use Freemium\SubscribableInterface;
use AktiveMerchant\Billing\CreditCard;

class CreditCardFailed extends DomainEvent
{
    const NAME = 'creditcard.failed';

    private $creditCard;

    private $subscribable;

    private $exception;

    public function __construct(
        CreditCard $creditCard,
        SubscribableInterface $subscribable,
        Throwable $exception
    ) {
        $this->creditCard = $creditCard;
        $this->subscribable = $subscribable;
        $this->exception = $exception;
    }

    public function getSubscribable() : SubscribableInterface
    {
        return $this->subscribable;
    }

    public function getCreditCard() : CreditCard
    {
        return $this->creditCard;
    }

    public function getException()
    {
        return $this->exception;
    }
}
