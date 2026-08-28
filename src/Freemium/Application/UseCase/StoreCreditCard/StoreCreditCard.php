<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\StoreCreditCard;

use Freemium\Domain\Subscribable;
use AktiveMerchant\Billing\CreditCard;

class StoreCreditCard
{
    public function __construct(
        private readonly CreditCard $creditCard,
        private readonly Subscribable $subscribable
    ) {
    }

    public function getCreditCard(): CreditCard
    {
        return $this->creditCard;
    }

    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }
}
