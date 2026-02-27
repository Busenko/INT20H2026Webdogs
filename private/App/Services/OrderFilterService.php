<?php
declare(strict_types=1);
namespace App\Services;

class OrderFilterService
{
    public function __construct(private \App\Gateways\OrderGateway $gateway) {}

    public function getFilteredOrders(array $queryParams): array
    {
        $filters = [
            'page'   => isset($queryParams['page']) ? max(1, (int)$queryParams['page']) : 1,
            'limit'  => isset($queryParams['limit']) ? max(1, (int)$queryParams['limit']) : 10,
            'county' => $queryParams['county'] ?? null,
            'sort'   => ($queryParams['sort'] ?? 'DESC'),
            'id'     => $queryParams['id'] ?? null,
            'lat'    => $queryParams['lat'] ?? null,
            'lon'    => $queryParams['lon'] ?? null,
        ];

        return $this->gateway->getListWithFilters($filters);
    }
}