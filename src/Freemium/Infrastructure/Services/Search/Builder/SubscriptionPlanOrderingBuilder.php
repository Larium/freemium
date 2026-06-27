<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search\Builder;

use Doctrine\DBAL\Query\QueryBuilder;
use Freemium\Infrastructure\Services\Search\SearchResource;
use Larium\Search\Criteria;
use Larium\Search\Doctrine\Dbal\Builder;

final class SubscriptionPlanOrderingBuilder implements Builder
{
    private const FIELD_MAP = [
        'name' => 'p.name',
        'token' => 'p.token',
    ];

    public function supports(Criteria $criteria): bool
    {
        return $criteria->resourceName === SearchResource::SUBSCRIPTION_PLANS;
    }

    public function build(Criteria $criteria, QueryBuilder $queryBuilder): void
    {
        $field = $criteria->ordering->field;
        if (!isset(self::FIELD_MAP[$field])) {
            $field = 'name';
        }

        $queryBuilder->orderBy(self::FIELD_MAP[$field], $criteria->ordering->direction);
    }
}
