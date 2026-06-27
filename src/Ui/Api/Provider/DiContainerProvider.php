<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Provider;

use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\Configuration;
use DI\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Freemium\Application\Event\EventProvider;
use Freemium\Application\UseCase\CommandBus as FreemiumCommandBus;
use Freemium\Domain\Clock;
use Freemium\Domain\Gateways\GatewayFactory;
use Freemium\Domain\IdGenerator;
use Freemium\Domain\Repository\CouponPlanRepository;
use Freemium\Domain\Repository\CouponRedemptionRepository;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionChangeRepository;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Domain\Repository\TransactionRepository;
use Freemium\Domain\SystemClock;
use Freemium\Domain\TrialEligibilityChecker;
use Freemium\Infrastructure\Service\CustomIdGenerator;
use Freemium\Infrastructure\FreemiumCommandBusResolver;
use Freemium\Infrastructure\Repository\RepositoryTrialEligibilityChecker;
use Freemium\Infrastructure\Gateway\BogusGatewayFactory;
use Freemium\Infrastructure\ORM\EntityManagerFactory;
use Freemium\Infrastructure\Repository\CouponRepository;
use Freemium\Infrastructure\Repository\DoctrineCouponPlanRepository;
use Freemium\Infrastructure\Repository\DoctrineCouponRedemptionRepository;
use Freemium\Infrastructure\Repository\DoctrineCouponRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscribableRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscriptionChangeRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscriptionPlanRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscriptionRepository;
use Freemium\Infrastructure\Repository\DoctrineTransactionRepository;
use Larium\Framework\Bridge\Routing\FastRouteBridge;
use Larium\Framework\Contract\Routing\Router;
use Larium\Framework\Provider\ContainerProvider;
use Larium\Ui\SharedKernel\Authentication\AuthenticatorService;
use Larium\Ui\SharedKernel\Authentication\CredentialCollector;
use Larium\Ui\SharedKernel\Authentication\Firewall;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function FastRoute\simpleDispatcher;

class DiContainerProvider implements ContainerProvider
{
    public function getContainer(): ContainerInterface
    {
        (Dotenv::createImmutable(__DIR__ . '/../../../../'))->safeLoad();
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);

        $builder->addDefinitions([
            EntityManagerInterface::class => static function (): EntityManager {
                return EntityManagerFactory::create($_ENV);
            },
            EntityManager::class => static fn (ContainerInterface $c) => $c->get(EntityManagerInterface::class),
            Connection::class => static fn (ContainerInterface $c) => $c->get(EntityManagerInterface::class)->getConnection(),
            EventProvider::class => static fn () => new EventProvider(),
            FreemiumCommandBusResolver::class => static fn (ContainerInterface $c) => new FreemiumCommandBusResolver($c),
            FreemiumCommandBus::class => static function (ContainerInterface $c) {
                return new FreemiumCommandBus(
                    $c->get(EventProvider::class),
                    $c->get(FreemiumCommandBusResolver::class)
                );
            },
            IdGenerator::class => static fn () => new CustomIdGenerator(),
            Clock::class => static fn () => new SystemClock(),
            GatewayFactory::class => static fn () => new BogusGatewayFactory(),
            TrialEligibilityChecker::class => static fn (ContainerInterface $c) => new RepositoryTrialEligibilityChecker(
                $c->get(SubscriptionRepository::class)
            ),
            SubscriptionRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineSubscriptionRepository::class),
            SubscribableRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineSubscribableRepository::class),
            SubscriptionPlanRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineSubscriptionPlanRepository::class),
            SubscriptionChangeRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineSubscriptionChangeRepository::class),
            CouponRedemptionRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineCouponRedemptionRepository::class),
            CouponPlanRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineCouponPlanRepository::class),
            TransactionRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineTransactionRepository::class),
            CouponRepository::class => static fn (ContainerInterface $c) => $c->get(DoctrineCouponRepository::class),
            ValidatorInterface::class => static fn () => Validation::createValidatorBuilder()
                ->enableAttributeMapping()
                ->getValidator(),
            AutoMapperInterface::class => static fn () => new AutoMapper(new Configuration()),
            Router::class => static function () {
                $dispatcher = simpleDispatcher(static function (RouteCollector $c) {
                    $routerProvider = new RouterProvider();
                    $routerProvider->register($c);
                });

                return new FastRouteBridge($dispatcher);
            },
            LoggerInterface::class => static function () {
                $log = new Logger(sprintf('%s-%s', $_ENV['APP_NAME'], $_ENV['APP_ENV']));
                $level = Level::Info;
                if ($_ENV['APP_ENV'] === 'development') {
                    $level = Level::Debug;
                }
                $log->pushHandler(new StreamHandler('php://stdout', $level));

                return $log;
            },
            AuthenticatorService::class => static function () {
                throw new \RuntimeException('AuthenticatorService not implemented');
            },
            CredentialCollector::class => static function () {
                throw new \RuntimeException('CredentialCollector not implemented');
            },
            Firewall::class => static function (ContainerInterface $c) {
                return new Firewall($c, [
                    '/^\/admin/' => 'adminAuthentication',
                    '/^\/secure/' => 'userAuthentication',
                ]);
            },
        ]);

        return $builder->build();
    }
}
