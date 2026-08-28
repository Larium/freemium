<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service\Search\Builder;

use Larium\Search\Criteria;
use Doctrine\DBAL\Query\QueryBuilder;
use Larium\Search\Doctrine\Dbal\Builder;
use Freemium\Infrastructure\Service\Search\SearchResource;

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
                's.trial_started_on',
                's.trial_ends_on',
                's.grace_started_on',
                's.grace_ends_on',
                's.cancel_at',
                'p.name AS plan_name'
            )
            ->from('subscriptions', 's')
            ->innerJoin('s', 'subscription_plans', 'p', 's.subscription_plan_token = p.token');
    }
}
