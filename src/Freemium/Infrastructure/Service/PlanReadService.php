<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service;

use Larium\Search\Criteria;
use Freemium\Infrastructure\Service\Search\SearchResource;
use Freemium\Infrastructure\Service\Search\DoctrineDbalSearchEngineFactory;

final class PlanReadService
{
    public function __construct(
        private readonly DoctrineDbalSearchEngineFactory $searchEngineFactory,
    ) {
    }

    public function getByName(string $name): ?array
    {
        $result = $this->search(['name' => $name, 'page' => 1, 'limit' => 1]);

        return $result['items'][0] ?? null;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $queryParams): array
    {
        $queryParams = array_merge(['sort' => 'name'], $queryParams);
        $criteria = Criteria::fromQueryParams(SearchResource::SUBSCRIPTION_PLANS, $queryParams);

        $result = $this->searchEngineFactory->create()->match($criteria);
        $result->setCountField('p.token');

        $limit = max(1, min(100, $criteria->paginating->itemsPerPage));
        $offset = $criteria->paginating->offset;

        return [
            'items' => array_map($this->createRow(...), $result->fetch($offset, $limit)),
            'total' => $result->count(),
        ];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function createRow(array $row): array
    {
        return [
            'token' => $row['token'],
            'name' => $row['name'],
            'period' => (int) $row['period'],
            'frequency' => (int) $row['frequency'],
            'rate' => [
                'amount' => $row['rate_amount'],
                'currency' => $row['rate_currency'],
            ],
        ];
    }
}
