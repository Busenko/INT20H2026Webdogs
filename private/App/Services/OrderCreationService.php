<?php
declare(strict_types=1);

namespace App\Services;

use App\Gateways\OrderGateway;
use App\Models\Order;
use Exception;

class OrderCreationService
{
    public function __construct(
        private OrderGateway $orderGateway,
        private JurisdictionService $jurisdictionService,
        private TaxCalculatorService $taxCalculator
    ) {}

public function createOrder(array $data): string
    {
        $lat = (float)($data['latitude'] ?? $data['lat'] ?? 0);
        $lon = (float)($data['longitude'] ?? $data['lon'] ?? 0);
        
        $rawSubtotal = str_replace(',', '.', (string)($data['subtotal'] ?? '0'));
        $subtotal = round((float)$rawSubtotal, 2);

        // Дістаємо дату з CSV
        $createdAt = $data['created_at'] ?? $data['date'] ?? date('Y-m-d H:i:s');

        $jurisdictionName = $this->jurisdictionService->getJurisdictionByCoordinates($lat, $lon);
        $taxData = $this->taxCalculator->calculate($jurisdictionName, $subtotal);

        // Створюємо об'єкт моделі
        if ($taxData === null) {
            $order = new Order(
                latitude: $lat,
                longitude: $lon,
                subtotal: $subtotal,
                tax_amount: 0.00,
                total_amount: $subtotal,
                id_tax: null,
                created_at: $createdAt
            );
        } else {
            $order = new Order(
                latitude: $lat,
                longitude: $lon,
                subtotal: $subtotal,
                tax_amount: $taxData['tax_amount'],
                total_amount: $subtotal + $taxData['tax_amount'],
                id_tax: (int)$taxData['id_tax'],
                created_at: $createdAt
            );
        }

        // ВИПРАВЛЕНО: Викликаємо метод create(), який точно є в OrderGateway
        return $this->orderGateway->create($order);
    }
}