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
        private PDO $db 
    ) {}

    public function import(string $filePath): array
    {
        // $handle = fopen($filePath, "r");
        // if (!$handle) {
        //     throw new Exception("Не вдалося відкрити файл");
        // }

        // $headers = fgetcsv($handle, 0, ",", "\"", "");
        // if (!$headers) {
        //     fclose($handle);
        //     throw new Exception("Файл порожній або некоректний");
        // }
        $fileContent = file_get_contents($filePath);
        if (!$fileContent) {
            throw new Exception("Не вдалося прочитати файл");
        }

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $fileContent);
        rewind($handle); 

        $headers = fgetcsv($handle, 0, ",", "\"", "");
        if (!$headers) {
            fclose($handle);
            throw new Exception("Файл порожній або некоректний");
        }

        $headers = array_map('strtolower', array_map('trim', $headers));
        
        $count = 0;
        $errors = [];

        $batchSize = 2500; 
        $batchData = [];
        $uniqueCoordinates = []; 

        while (($row = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
           
            if (count($headers) !== count($row)) continue;

            $data = array_combine($headers, $row);

            $lat = (float)($data['latitude'] ?? $data['lat'] ?? 0);
            $lon = (float)($data['longitude'] ?? $data['lon'] ?? 0);

            $batchData[] = [
                'latitude'  => $lat,
                'longitude' => $lon,
                'subtotal'  => $data['subtotal'] ?? 0,
                'timestamp' => $data['timestamp'] ?? $data['created_at'] ?? $data['date'] ?? null
            ];

            $coordKey = "{$lat}_{$lon}";
            if (!isset($uniqueCoordinates[$coordKey])) {
                $uniqueCoordinates[$coordKey] = ['lat' => $lat, 'lon' => $lon];
            }

            if (count($batchData) >= $batchSize) {
                $this->processBatch($batchData, array_values($uniqueCoordinates), $count, $errors);
                $batchData = [];
                $uniqueCoordinates = [];
            }
        }
        
        if (!empty($batchData)) {
            $this->processBatch($batchData, array_values($uniqueCoordinates), $count, $errors);
        }

        fclose($handle);
        
        return [
            'status' => 'success', 
            'count'  => $count, 
            'failed' => count($errors),
            'errors' => $errors
        ];
    }

    private function processBatch(array $batchData, array $coordinates, int &$count, array &$errors): void
    {
        try {
            $this->jurisdictionService->prepareCacheForCoordinates($coordinates);

            $this->db->beginTransaction();
            $insertedCount = $this->orderCreationService->createBatch($batchData);
            $this->db->commit();
            
            $count += $insertedCount;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $errors[] = "Помилка пакету: " . $e->getMessage();
        }
    }
}