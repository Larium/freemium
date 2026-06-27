<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search\Builder;

use Doctrine\DBAL\Query\QueryBuilder;
use Freemium\Infrastructure\Services\Search\SearchResource;
use Larium\Search\Criteria;
use Larium\Search\Doctrine\Dbal\Builder;

final class SubscriptionTokenFilterBuilder implements Builder
{
    public function supports(Criteria $criteria): bool
    {
        return $criteria->resourceName === SearchResource::SUBSCRIPTIONS
            && isset($criteria->filtering->fields['token']);
    }

    public function build(Criteria $criteria, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->where('s.token = :token')
            ->setParameter('token', $criteria->filtering->fields['token']);
    }
}
