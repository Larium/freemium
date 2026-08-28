<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Freemium\Domain\SubscriptionStatus;

final class SubscriptionStatusType extends Type
{
    public const NAME = 'subscription_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 32]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SubscriptionStatus
    {
        if ($value === null) {
            return null;
        }

        return SubscriptionStatus::from((string) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof SubscriptionStatus ? $value->value : (string) $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
