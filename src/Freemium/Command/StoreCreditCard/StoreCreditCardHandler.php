<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard;

use Freemium\Freemium;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Event\Subscribable\CreditCardStored;
use Freemium\Repository\SubscribableRepositoryInterface;

class StoreCreditCardHandler extends AbstractCommandHandler
{
    private $repository;

    public function __construct(
        EventProvider $eventProvider,
        SubscribableRepositoryInterface $repository
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
    }

    public function handle(StoreCreditCard $command)
    {
        $subscribable = $command->getSubscribable();

        $creditCard = $command->getCreditCard();
        $gateway = Freemium::getGateway();
        $response = $gateway->store($creditCard);

        $subscribable->setBillingKey($response->authorization());
        $this->repository->insert($subscribable);

        $event = new CreditCardStored($creditCard, $subscribable);
        $this->getEventProvider()->raise($event);
    }
}
