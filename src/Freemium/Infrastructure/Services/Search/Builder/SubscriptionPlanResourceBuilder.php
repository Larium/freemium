<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search\Builder;

use Doctrine\DBAL\Query\QueryBuilder;
use Freemium\Infrastructure\Services\Search\SearchResource;
use Larium\Search\Criteria;
use Larium\Search\Doctrine\Dbal\Builder;

final class SubscriptionPlanResourceBuilder implements Builder
{
    public function supports(Criteria $criteria): bool
    {
        return $criteria->resourceName === SearchResource::SUBSCRIPTION_PLANS;
    }

    public function build(Criteria $criteria, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->select(
                'p.token',
                'p.name',
                'p.period',
                'p.frequency',
                'p.rate_amount',
                'p.rate_currency'
            )
            ->from('subscription_plans', 'p');
    }
}
