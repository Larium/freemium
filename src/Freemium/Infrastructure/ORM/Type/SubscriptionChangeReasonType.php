<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Freemium\Domain\SubscriptionChangeReason;

final class SubscriptionChangeReasonType extends Type
{
    public const NAME = 'subscription_change_reason';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SubscriptionChangeReason
    {
        if ($value === null) {
            return null;
        }

        return SubscriptionChangeReason::from((int) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof SubscriptionChangeReason ? $value->value : (int) $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
