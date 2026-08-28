<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Larium\Framework\Http\Action;
use Psr\Http\Message\ResponseInterface;
use Larium\Ui\Api\Responder\JsonResponder;
use Psr\Http\Message\ServerRequestInterface;
use Freemium\Infrastructure\Service\PlanReadService;

final class GetPlansAction implements Action
{
    public function __construct(
        private readonly PlanReadService $planRead,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $payload = $this->planRead->search($request->getQueryParams());

        return $this->responder->getResponse($payload, 200);
    }
}
