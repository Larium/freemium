<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service\Search\Builder;

use Larium\Search\Criteria;
use Doctrine\DBAL\Query\QueryBuilder;
use Larium\Search\Doctrine\Dbal\Builder;
use Freemium\Infrastructure\Service\Search\SearchResource;

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
