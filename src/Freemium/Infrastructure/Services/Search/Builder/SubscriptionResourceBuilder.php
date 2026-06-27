<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search\Builder;

use Doctrine\DBAL\Query\QueryBuilder;
use Freemium\Infrastructure\Services\Search\SearchResource;
use Larium\Search\Criteria;
use Larium\Search\Doctrine\Dbal\Builder;

final class SubscriptionResourceBuilder implements Builder
{
    public function supports(Criteria $criteria): bool
    {
        return $criteria->resourceName === SearchResource::SUBSCRIPTIONS;
    }

    public function build(Criteria $criteria, QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->select(
                's.token',
                's.status',
                's.paid_through',
                's.started_on',
                's.in_trial',
                's.days_trial',
                's.days_grace',
                's.cancel_at',
                'p.name AS plan_name'
            )
            ->from('subscriptions', 's')
            ->innerJoin('s', 'subscription_plans', 'p', 's.subscription_plan_token = p.token');
    }
}
