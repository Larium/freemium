<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard;

use Throwable;
use RuntimeException;
use Freemium\Freemium;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Repository\SubscribableRepository;
use Freemium\Event\Subscribable\CreditCardFailed;
use Freemium\Event\Subscribable\CreditCardStored;

class StoreCreditCardHandler extends AbstractCommandHandler
{
    private $repository;

    public function __construct(
        EventProvider $eventProvider,
        SubscribableRepository $repository
    ) {
        parent::__construct($eventProvider);
        $this->repository = $repository;
    }

    public function handle(StoreCreditCard $command)
    {
        $subscribable = $command->getSubscribable();
        $creditCard = $command->getCreditCard();

        $event = new Event\CreditCardStored($creditCard, $subscribable);
        try {
            $gateway = Freemium::getGateway();
            $response = $gateway->store($creditCard);
            if (false === $response->success()) {
                throw new RuntimeException($response->message());
            }

            $subscribable->setBillingKey($response->authorization());
            $this->repository->insert($subscribable);
        } catch (Throwable $e) {
            $event = new Event\CreditCardFailed(
                $creditCard,
                $subscribable,
                $e
            );
            throw $e;
        } finally {
            $this->getEventProvider()->raise($event);
        }
    }
}
