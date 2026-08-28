<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Freemium\Infrastructure\ORM\Type\SubscriptionChangeReasonType;
use Freemium\Infrastructure\ORM\Type\SubscriptionStatusType;

final class EntityManagerFactory
{
    public static function create(array $env): EntityManager
    {
        $isDev = ($env['APP_ENV'] ?? 'production') === 'development';

        $config = ORMSetup::createXMLMetadataConfiguration(
            paths: [__DIR__ . '/Mapping'],
            isDevMode: $isDev,
        );

        if (PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

        self::registerTypes();

        $connection = DriverManager::getConnection(self::connectionParams($env), $config);

        return new EntityManager($connection, $config);
    }

    /**
     * @return array<string, mixed>
     */
    private static function connectionParams(array $env): array
    {
        if (isset($env['DATABASE_URL']) && is_string($env['DATABASE_URL']) && $env['DATABASE_URL'] !== '') {
            return [
                'driver' => 'pdo_pgsql',
                'url' => $env['DATABASE_URL'],
            ];
        }

        return [
            'driver' => 'pdo_pgsql',
            'host' => $env['DB_HOST'] ?? 'postgres',
            'port' => (int) ($env['DB_PORT'] ?? 5432),
            'dbname' => $env['DB_NAME'] ?? 'freemium',
            'user' => $env['DB_USER'] ?? 'freemium',
            'password' => $env['DB_PASSWORD'] ?? 'freemium',
            'charset' => 'utf8',
        ];
    }

    private static function registerTypes(): void
    {
        if (!Type::hasType(SubscriptionStatusType::NAME)) {
            Type::addType(SubscriptionStatusType::NAME, SubscriptionStatusType::class);
        }

        if (!Type::hasType(SubscriptionChangeReasonType::NAME)) {
            Type::addType(SubscriptionChangeReasonType::NAME, SubscriptionChangeReasonType::class);
        }
    }
}
