<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Larium\Framework\Http\Action;
use Psr\Http\Message\ResponseInterface;
use Larium\Ui\Api\Responder\JsonResponder;
use Psr\Http\Message\ServerRequestInterface;
use Freemium\Infrastructure\Service\SubscriptionReadService;
use Freemium\Domain\Repository\Exception\EntityNotFoundException;

final class GetSubscriptionAction implements Action
{
    public function __construct(
        private readonly SubscriptionReadService $subscriptionRead,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $token = (string) $request->getAttribute('id');
        $payload = $this->subscriptionRead->getByToken($token);
        if ($payload === null) {
            throw new EntityNotFoundException('Subscription not found');
        }

        return $this->responder->getResponse($payload, 200);
    }
}
