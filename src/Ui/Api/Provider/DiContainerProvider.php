<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Provider;

use Dotenv\Dotenv;
use Monolog\Level;
use Monolog\Logger;
use DI\ContainerBuilder;
use Freemium\Domain\Clock;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use FastRoute\RouteCollector;
use AutoMapperPlus\AutoMapper;
use Doctrine\ORM\EntityManager;
use Freemium\Domain\IdGenerator;
use Freemium\Domain\SystemClock;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validation;
use Freemium\Domain\Gateways\GatewayFactory;
use Freemium\Domain\TrialEligibilityChecker;
use Freemium\Application\Event\EventProvider;
use Larium\Framework\Contract\Routing\Router;
use Larium\Framework\Provider\ContainerProvider;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use Freemium\Application\UseCase\ContainerResolver;
use Larium\Ui\SharedKernel\Authentication\Firewall;
use Freemium\Domain\Repository\CouponPlanRepository;
use Larium\Framework\Bridge\Routing\FastRouteBridge;
use Freemium\Domain\Repository\TransactionRepository;
use Freemium\Infrastructure\ORM\EntityManagerFactory;
use Freemium\Domain\Repository\SubscribableRepository;
use Freemium\Domain\Repository\SubscriptionRepository;
use Freemium\Infrastructure\Service\CustomIdGenerator;
use Freemium\Infrastructure\Gateway\BogusGatewayFactory;
use Freemium\Infrastructure\Repository\CouponRepository;
use Freemium\Domain\Repository\CouponRedemptionRepository;
use Freemium\Domain\Repository\SubscriptionPlanRepository;
use Freemium\Domain\Repository\SubscriptionChangeRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Larium\Ui\SharedKernel\Authentication\CredentialCollector;
use Larium\Ui\SharedKernel\Authentication\AuthenticatorService;
use Freemium\Infrastructure\Repository\DoctrineCouponRepository;
use Freemium\Application\UseCase\CommandBus as FreemiumCommandBus;
use Freemium\Infrastructure\Repository\DoctrineCouponPlanRepository;
use Freemium\Infrastructure\Repository\DoctrineTransactionRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscribableRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscriptionRepository;
use Freemium\Infrastructure\Repository\RepositoryTrialEligibilityChecker;
use Freemium\Infrastructure\Repository\DoctrineCouponRedemptionRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscriptionPlanRepository;
use Freemium\Infrastructure\Repository\DoctrineSubscriptionChangeRepository;

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
            ContainerResolver::class => static fn (ContainerInterface $c) => new ContainerResolver($c),
            FreemiumCommandBus::class => static function (ContainerInterface $c) {
                return new FreemiumCommandBus(
                    $c->get(ContainerResolver::class)
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
            AutoMapperInterface::class => static fn () => new AutoMapper(new AutoMapperConfig()),
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
