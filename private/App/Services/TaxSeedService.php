<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tax;
use Exception;

class TaxSeedService
{
    public function __construct(
        private object $gateway,
        private object $validator
    ) {}

    public function syncOfficialTaxData(): array
    {
        $jsonPath = $this->getStoragePath('ny_taxes_2025.json');

        if (!file_exists($jsonPath)) {
            throw new Exception("Файл даних не знайдено: {$jsonPath}");
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Помилка JSON: " . json_last_error_msg());
        }

        $importedCount = 0;

        foreach ($data as $item) {
            $taxData = $this->mapJsonToTaxData($item);
            $errors = $this->validator->validate($taxData);

            if (empty($errors)) {
                $taxModel = Tax::fromArray($taxData);
                $this->gateway->upsert($taxModel);
                $importedCount++;
            } else {
                echo "[Валідація] Помилка для " . ($item['jurisdiction'] ?? 'Unknown') . ": " . implode(', ', $errors) . PHP_EOL;
            }
        }

        return [
            'imported' => $importedCount,
            'status'   => 'success',
            'source'   => $jsonPath
        ];
    }

private function mapJsonToTaxData(array $item): array
    {
        // Очищаємо назву від зірочок, якщо вони там були
        $jurisdiction = trim(str_replace('*', '', $item['jurisdiction'] ?? 'Unknown'));
        $reportingCode = (string)($item['reporting_code'] ?? '');
        $totalRate = (float)($item['sales_tax_rate'] ?? 0) / 100; // 8.875 -> 0.08875

        // 1. Офіційна базова ставка штату NY
        $stateRate = 0.04;

        // 2. Офіційний перелік зон MCTD (Metropolitan Commuter Transportation District)
        $mctdZones = [
            'New York City', 'Dutchess', 'Nassau', 'Orange', 
            'Putnam', 'Rockland', 'Suffolk', 'Westchester'
        ];

        // Перевіряємо, чи входить юрисдикція до зони MCTD
        $specialRate = 0.0;
        foreach ($mctdZones as $zone) {
            if (stripos($jurisdiction, $zone) !== false) {
                $specialRate = 0.00375;
                break;
            }
        }

        // 3. Загальний місцевий залишок (Total - State - MCTD)
        // Використовуємо round() до 5 знаків, щоб уникнути похибки чисел з плаваючою комою в PHP
        $localRemainder = round($totalRate - $stateRate - $specialRate, 5);

        // 4. Логіка розподілу: Місто чи Округ?
        // Якщо в назві є слово "City" (наприклад, New York City) - це місто. Інакше - округ.
        $isCity = stripos($jurisdiction, 'City') !== false;

        $countyRate = $isCity ? 0.0 : $localRemainder;
        $cityRate   = $isCity ? $localRemainder : 0.0;

        return [
            'jurisdictions'      => $jurisdiction,
            'reporting_code'     => $reportingCode,
            'composite_tax_rate' => $totalRate,
            'state_rate'         => $stateRate,
            'county_rate'        => $countyRate,
            'city_rate'         => $cityRate,
            'special_rates'      => $specialRate,
            'created_at'         => date('Y-m-d H:i:s')
        ];
    }

    private function getStoragePath(string $filename): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $filename;
    }
}