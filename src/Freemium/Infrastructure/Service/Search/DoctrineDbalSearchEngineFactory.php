<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service\Search;

use Doctrine\DBAL\Connection;
use Larium\Search\Doctrine\Dbal\DoctrineDbalSearchEngine;
use Freemium\Infrastructure\Service\Search\Builder\SubscriptionOrderingBuilder;
use Freemium\Infrastructure\Service\Search\Builder\SubscriptionResourceBuilder;
use Freemium\Infrastructure\Service\Search\Builder\SubscriptionTokenFilterBuilder;
use Freemium\Infrastructure\Service\Search\Builder\SubscriptionPlanOrderingBuilder;
use Freemium\Infrastructure\Service\Search\Builder\SubscriptionPlanResourceBuilder;
use Freemium\Infrastructure\Service\Search\Builder\SubscriptionPlanNameFilterBuilder;

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
