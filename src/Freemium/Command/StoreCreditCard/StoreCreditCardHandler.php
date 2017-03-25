<?php

declare(strict_types=1);

namespace Freemium\Command\StoreCreditCard;

use Throwable;
use RuntimeException;
use Freemium\Freemium;
use Freemium\Event\EventProvider;
use Freemium\Command\AbstractCommandHandler;
use Freemium\Event\Subscribable\CreditCardStored;
use Freemium\Event\Subscribable\CreditCardFailed;
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

        try {
            $gateway = Freemium::getGateway();
            $response = $gateway->store($creditCard);
            if (false === $response->success()) {
                throw new RuntimeException($response->message());
            }

            $subscribable->setBillingKey($response->authorization());
            $this->repository->insert($subscribable);
            $event = new CreditCardStored($creditCard, $subscribable);
        } catch (Throwable $e) {
            $event = new CreditCardFailed(
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
