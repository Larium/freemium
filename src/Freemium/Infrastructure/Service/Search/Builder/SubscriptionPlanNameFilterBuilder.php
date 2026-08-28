<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service\Search\Builder;

use Larium\Search\Criteria;
use Doctrine\DBAL\Query\QueryBuilder;
use Larium\Search\Doctrine\Dbal\Builder;
use Freemium\Infrastructure\Service\Search\SearchResource;

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
