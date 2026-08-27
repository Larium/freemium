<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service;

use Larium\Search\Criteria;
use Freemium\Infrastructure\Service\Search\SearchResource;
use Freemium\Infrastructure\Service\Search\DoctrineDbalSearchEngineFactory;

final class SubscriptionReadService
{
    public function __construct(
        private readonly DoctrineDbalSearchEngineFactory $searchEngineFactory,
    ) {
    }

    public function getByToken(string $token): ?array
    {
        $result = $this->search(['token' => $token, 'page' => 1, 'limit' => 1]);

        return $result['items'][0] ?? null;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $queryParams): array
    {
        $queryParams = array_merge(['sort' => '-started_on'], $queryParams);
        $criteria = Criteria::fromQueryParams(SearchResource::SUBSCRIPTIONS, $queryParams);

        $result = $this->searchEngineFactory->create()->match($criteria);
        $result->setCountField('s.token');

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
            'plan' => $row['plan_name'],
            'status' => $row['status'],
            'paidThrough' => $row['paid_through'],
            'startedOn' => $row['started_on'],
            'inTrial' => (bool) $row['in_trial'],
            'daysTrial' => (int) $row['days_trial'],
            'daysGrace' => (int) $row['days_grace'],
            'cancelAt' => $row['cancel_at'],
        ];
    }
}
