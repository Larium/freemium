<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Freemium\Application\UseCase\ChangePlan\ChangePlan;
use Freemium\Application\UseCase\CommandBus;
use Freemium\Domain\Repository\Exception\EntityNotFoundException;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Infrastructure\Services\SubscriptionReadService;
use Larium\Framework\Http\Action;
use Larium\Ui\Api\Action\Billing\Request\ChangePlanRequest;
use Larium\Ui\Api\Responder\JsonResponder;
use Larium\Ui\SharedKernel\Service\RequestObjectProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ChangePlanAction implements Action
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly SubscriptionPlanRepository $planRepository,
        private readonly SubscriptionReadService $subscriptionRead,
        private readonly RequestObjectProvider $requestProvider,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $token = (string) $request->getAttribute('id');
        $dto = $this->requestProvider->provide($request, ChangePlanRequest::class, []);

        $subscription = $this->subscriptionRepository->findByToken($token);
        if ($subscription === null) {
            throw new EntityNotFoundException('Subscription not found');
        }

        $plan = $this->planRepository->findByName($dto->planName);
        $this->commandBus->handle(new ChangePlan($subscription, $plan));

        $payload = $this->subscriptionRead->getByToken($token);

        return $this->responder->getResponse($payload ?? [], 200);
    }
}
