<?php

declare(strict_types=1);

namespace Freemium\Application\UseCase\StoreCreditCard;

use Throwable;
use RuntimeException;
use Freemium\Domain\Gateways\GatewayFactory;
use Freemium\Application\Event\EventProvider;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Application\UseCase\AbstractCommandHandler;

class StoreCreditCardHandler extends AbstractCommandHandler
{
    public function __construct(
        EventProvider $eventProvider,
        private readonly SubscribableRepository $repository,
        private readonly GatewayFactory $gatewayFactory
    ) {
        parent::__construct($eventProvider);
    }

    public function handle(StoreCreditCard $command)
    {
        $subscribable = $command->getSubscribable();
        $creditCard = $command->getCreditCard();

        $event = new Event\CreditCardStored($creditCard, $subscribable);
        try {
            $gateway = $this->gatewayFactory->getGatewayFor($subscribable);
            $response = $gateway->store($creditCard);
            if (!$response->success()) {
                throw new RuntimeException(
                    $response->message() . ' (customer: ' . $subscribable->getCustomerId() . ')'
                );
            }

            $subscribable->updateBillingKey($response->authorization());
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
