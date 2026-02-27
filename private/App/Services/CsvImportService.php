<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use Exception;

class CsvImportService
{
    public function __construct(
        private OrderCreationService $orderCreationService,
        private JurisdictionService $jurisdictionService,
        private PDO $db // Конструктор очікує системний PDO
    ) {}

    public function import(string $filePath): array
    {
        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new Exception("Не вдалося відкрити файл");
        }

        $headers = fgetcsv($handle, 0, ",", "\"", "");
        if (!$headers) {
            fclose($handle);
            throw new Exception("Файл порожній або некоректний");
        }

        $headers = array_map('strtolower', array_map('trim', $headers));
        
        $count = 0;
        $errors = [];
        $batchSize = 1000;
        $batchData = [];

        while (($row = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
            // Перевірка на відповідність кількості колонок
            if (count($headers) !== count($row)) continue;

            $data = array_combine($headers, $row);

            $batchData[] = [
                'latitude'  => $data['latitude'] ?? $data['lat'] ?? 0,
                'longitude' => $data['longitude'] ?? $data['lon'] ?? 0,
                'subtotal'  => $data['subtotal'] ?? 0,
                'timestamp' => $data['timestamp'] ?? null
            ];

            if (count($batchData) >= $batchSize) {
                $this->processBatch($batchData, $count, $errors);
                $batchData = [];
            }
        }
        
        if (!empty($batchData)) {
            $this->processBatch($batchData, $count, $errors);
        }

        fclose($handle);
        
        return [
            'status' => 'success', 
            'count'  => $count, 
            'failed' => count($errors),
            'errors' => $errors
        ];
    }

    private function processBatch(array $batchData, int &$count, array &$errors): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($batchData as $data) {
                try {
                    $this->orderCreationService->createOrder($data);
                    $count++;
                } catch (Exception $e) {
                    $errors[] = "Помилка: " . $e->getMessage();
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $errors[] = "Помилка бази даних: " . $e->getMessage();
        }
    }
}