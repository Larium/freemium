<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Freemium\Domain\Repository\Exception\EntityNotFoundException;
use Freemium\Infrastructure\Services\PlanReadService;
use Larium\Framework\Http\Action;
use Larium\Ui\Api\Responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetPlanAction implements Action
{
    public function __construct(
        private readonly PlanReadService $planRead,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $name = (string) $request->getAttribute('name');
        $payload = $this->planRead->getByName($name);
        if ($payload === null) {
            throw new EntityNotFoundException('Plan not found');
        }

        return $this->responder->getResponse($payload, 200);
    }
}
