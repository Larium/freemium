<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use AktiveMerchant\Billing\CreditCard;
use Freemium\Application\UseCase\CommandBus;
use Freemium\Application\UseCase\StoreCreditCard\StoreCreditCard;
use Freemium\Domain\Repository\SubscribableRepository;
use Larium\Framework\Http\Action;
use Larium\Ui\Api\Action\Billing\Request\StorePaymentMethodRequest;
use Larium\Ui\Api\Responder\JsonResponder;
use Larium\Ui\SharedKernel\Service\RequestObjectProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class StorePaymentMethodAction implements Action
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly SubscribableRepository $subscribableRepository,
        private readonly RequestObjectProvider $requestProvider,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $customerId = (string) $request->getAttribute('id');
        $dto = $this->requestProvider->provide($request, StorePaymentMethodRequest::class, []);

        $subscribable = $this->subscribableRepository->findByCustomerId($customerId);

        $creditCard = new CreditCard([
            'number' => $dto->number,
            'month' => $dto->month,
            'year' => $dto->year,
            'verification_value' => $dto->verificationValue,
        ]);

        $this->commandBus->handle(new StoreCreditCard($creditCard, $subscribable));

        return $this->responder->getResponse(['customerId' => $customerId], 200);
    }
}
