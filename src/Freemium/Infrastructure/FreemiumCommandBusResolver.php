<?php

declare(strict_types=1);

namespace Freemium\Infrastructure;

use Freemium\Application\Event\EventProvider;
use Freemium\Application\UseCase\ApplyCoupon\ApplyCoupon;
use Freemium\Application\UseCase\ApplyCoupon\ApplyCouponHandler;
use Freemium\Application\UseCase\ChangePlan\ChangePlan;
use Freemium\Application\UseCase\ChangePlan\ChangePlanHandler;
use Freemium\Application\UseCase\CreateSubscription\NewSubscription;
use Freemium\Application\UseCase\CreateSubscription\NewSubscriptionHandler;
use Freemium\Application\UseCase\StoreCreditCard\StoreCreditCard;
use Freemium\Application\UseCase\StoreCreditCard\StoreCreditCardHandler;
use Freemium\Domain\Clock;
use Freemium\Domain\Gateways\GatewayFactory;
use Freemium\Domain\Repository\CouponPlanRepository;
use Freemium\Domain\Repository\CouponRedemptionRepository;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionChangeRepository;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Domain\TrialEligibilityChecker;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

final class FreemiumCommandBusResolver
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(object $command, EventProvider $eventProvider): object
    {
        return match ($command::class) {
            NewSubscription::class => new NewSubscriptionHandler(
                $eventProvider,
                $this->container->get(SubscriptionRepository::class),
                $this->container->get(SubscriptionChangeRepository::class),
                $this->container->get(SubscribableRepository::class),
                $this->container->get(SubscriptionPlanRepository::class),
                $this->container->get(TrialEligibilityChecker::class),
                $this->container->get(Clock::class),
            ),
            ChangePlan::class => new ChangePlanHandler(
                $eventProvider,
                $this->container->get(SubscriptionRepository::class),
                $this->container->get(SubscriptionChangeRepository::class),
            ),
            StoreCreditCard::class => new StoreCreditCardHandler(
                $eventProvider,
                $this->container->get(SubscribableRepository::class),
                $this->container->get(GatewayFactory::class),
            ),
            ApplyCoupon::class => new ApplyCouponHandler(
                $eventProvider,
                $this->container->get(CouponRedemptionRepository::class),
                $this->container->get(CouponPlanRepository::class),
            ),
            default => throw new InvalidArgumentException('No handler for ' . $command::class),
        };
    }
}
