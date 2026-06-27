<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search\Builder;

use Doctrine\DBAL\Query\QueryBuilder;
use Freemium\Infrastructure\Services\Search\SearchResource;
use Larium\Search\Criteria;
use Larium\Search\Doctrine\Dbal\Builder;

final class SubscriptionPlanNameFilterBuilder implements Builder
{
    public function supports(Criteria $criteria): bool
    {
        return $criteria->resourceName === SearchResource::SUBSCRIPTION_PLANS
            && isset($criteria->filtering->fields['name']);
    }

    public function build(Criteria $criteria, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->where('p.name = :name')
            ->setParameter('name', $criteria->filtering->fields['name']);
    }
}
