<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Freemium\Infrastructure\Services\PlanReadService;
use Larium\Framework\Http\Action;
use Larium\Ui\Api\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
