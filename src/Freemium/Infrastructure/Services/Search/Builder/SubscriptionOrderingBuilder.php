<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search\Builder;

use Doctrine\DBAL\Query\QueryBuilder;
use Freemium\Infrastructure\Services\Search\SearchResource;
use Larium\Search\Criteria;
use Larium\Search\Doctrine\Dbal\Builder;

final class SubscriptionOrderingBuilder implements Builder
{
    private const FIELD_MAP = [
        'token' => 's.token',
        'status' => 's.status',
        'started_on' => 's.started_on',
        'paid_through' => 's.paid_through',
        'plan' => 'p.name',
    ];

    public function supports(Criteria $criteria): bool
    {
        return $criteria->resourceName === SearchResource::SUBSCRIPTIONS;
    }

    public function build(Criteria $criteria, QueryBuilder $queryBuilder): void
    {
        $field = $criteria->ordering->field;
        $direction = $criteria->ordering->direction;

        if (!isset(self::FIELD_MAP[$field])) {
            $field = 'started_on';
            $direction = Criteria\Ordering::DESC;
        }

        $queryBuilder->orderBy(self::FIELD_MAP[$field], $direction);
    }
}
