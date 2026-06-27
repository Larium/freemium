<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Services\Search;

use Doctrine\DBAL\Connection;
use Freemium\Infrastructure\Services\Search\Builder\SubscriptionOrderingBuilder;
use Freemium\Infrastructure\Services\Search\Builder\SubscriptionPlanNameFilterBuilder;
use Freemium\Infrastructure\Services\Search\Builder\SubscriptionPlanOrderingBuilder;
use Freemium\Infrastructure\Services\Search\Builder\SubscriptionPlanResourceBuilder;
use Freemium\Infrastructure\Services\Search\Builder\SubscriptionResourceBuilder;
use Freemium\Infrastructure\Services\Search\Builder\SubscriptionTokenFilterBuilder;
use Larium\Search\Doctrine\Dbal\DoctrineDbalSearchEngine;

final class DoctrineDbalSearchEngineFactory
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function create(): DoctrineDbalSearchEngine
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return (new DoctrineDbalSearchEngine($queryBuilder))
            ->add(new SubscriptionPlanResourceBuilder())
            ->add(new SubscriptionResourceBuilder())
            ->add(new SubscriptionPlanNameFilterBuilder())
            ->add(new SubscriptionTokenFilterBuilder())
            ->add(new SubscriptionPlanOrderingBuilder())
            ->add(new SubscriptionOrderingBuilder());
    }
}
