<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing;

use Freemium\Domain\IdGenerator;
use Larium\Framework\Http\Action;
use Freemium\Domain\CouponRedemption;
use Psr\Http\Message\ResponseInterface;
use Larium\Ui\Api\Responder\JsonResponder;
use Freemium\Application\UseCase\CommandBus;
use Psr\Http\Message\ServerRequestInterface;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Infrastructure\Repository\CouponRepository;
use Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon;
use Larium\Ui\SharedKernel\Service\RequestObjectProvider;
use Freemium\Infrastructure\Service\SubscriptionReadService;
use Larium\Ui\Api\Action\Billing\Request\ApplyCouponRequest;
use Freemium\Domain\Repository\Exception\EntityNotFoundException;

final class ApplyCouponAction implements Action
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly CouponRepository $couponRepository,
        private readonly IdGenerator $idGenerator,
        private readonly SubscriptionReadService $subscriptionRead,
        private readonly RequestObjectProvider $requestProvider,
        private readonly JsonResponder $responder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $token = (string) $request->getAttribute('id');
        $dto = $this->requestProvider->provide($request, ApplyCouponRequest::class, []);

        $subscription = $this->subscriptionRepository->findByToken($token);
        if ($subscription === null) {
            throw new EntityNotFoundException('Subscription not found');
        }

        $coupon = $this->couponRepository->findByCode($dto->code);
        if ($coupon === null) {
            throw new EntityNotFoundException('Coupon not found');
        }

        $redemptionToken = $this->idGenerator->generate(CouponRedemption::TOKEN_PREFIX);
        $this->commandBus->handle(new ApplyCoupon($subscription, $coupon, $redemptionToken));

        $payload = $this->subscriptionRead->getByToken($token);

        return $this->responder->getResponse($payload ?? [], 200);
    }
}
