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
        $jurisdiction = trim(str_replace('*', '', $item['jurisdiction'] ?? 'Unknown'));
        $reportingCode = (string)($item['reporting_code'] ?? '');
        $totalRate = (float)($item['sales_tax_rate'] ?? 0) / 100;

        $stateRate = 0.04;
        $mctdZones = [
            'New York City', 'Dutchess', 'Nassau', 'Orange', 
            'Putnam', 'Rockland', 'Suffolk', 'Westchester'
        ];

        $specialRate = 0.0;
        foreach ($mctdZones as $zone) {
            if (stripos($jurisdiction, $zone) !== false) {
                $specialRate = 0.00375;
                break;
            }
        }

        $localRemainder = round($totalRate - $stateRate - $specialRate, 5);

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
      return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resurses' . DIRECTORY_SEPARATOR . $filename;
}
}