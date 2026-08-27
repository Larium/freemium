<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Freemium\Domain\IdGenerator;
use Freemium\Domain\Subscription;
use Larium\Framework\Http\Action;
use Psr\Http\Message\ResponseInterface;
use Larium\Ui\Api\Responder\JsonResponder;
use Freemium\Application\UseCase\CommandBus;
use Psr\Http\Message\ServerRequestInterface;
use Larium\Ui\SharedKernel\Service\RequestObjectProvider;
use Freemium\Infrastructure\Service\SubscriptionReadService;
use Larium\Ui\Api\Action\Billing\Request\CreateSubscriptionRequest;
use Freemium\Application\UseCase\CreateSubscription\NewSubscription;

final class CreateSubscriptionAction implements Action
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly IdGenerator $idGenerator,
        private readonly SubscriptionReadService $subscriptionRead,
        private readonly RequestObjectProvider $requestProvider,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $dto = $this->requestProvider->provide($request, CreateSubscriptionRequest::class, []);
        $token = $this->idGenerator->generate(Subscription::TOKEN_PREFIX);

        $this->commandBus->handle(new NewSubscription(
            $token,
            $dto->customerId,
            $dto->planName,
            $dto->daysTrial ?? 0,
            $dto->daysGrace ?? 0
        ));

        $payload = $this->subscriptionRead->getByToken($token);

        return $this->responder->getResponse($payload ?? [], 201);
    }
}
