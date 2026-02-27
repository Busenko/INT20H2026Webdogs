<?php

declare(strict_types=1);


require_once dirname(__DIR__) . '/private/vendor/autoload.php';

$privatePath = dirname(__DIR__) . '/private';
if (file_exists($privatePath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($privatePath);
    $dotenv->load();
}

set_error_handler("App\Core\ErrorHandler::handleError");
set_exception_handler("App\Core\ErrorHandler::handleException");

set_time_limit(0); 

use App\Core\Database;
use App\Gateways\OrderGateway;
use App\Gateways\TaxGateway;
use App\Services\JurisdictionService;
use App\Services\TaxCalculatorService;
use App\Services\OrderCreationService;
use App\Services\CsvImportService;

try {
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }

    $startTime = microtime(true);

    echo "--- [1/4] Ініціалізація бази даних ---" . PHP_EOL;
    
    $database = new Database(
        $_ENV['DB_HOST'] ?? "localhost", 
        $_ENV['DB_NAME'] ?? "order_db", 
        $_ENV['DB_USER'] ?? "root", 
        $_ENV['DB_PASS'] ?? ""
    );
    $pdo = $database->getConnection();

    echo "--- [2/4] Підготовка сервісів та ГЕО-даних ---" . PHP_EOL;
    
    $jurisdictionService = new JurisdictionService();


    $filePath = dirname(__DIR__) . '/private/App/Resurses/BetterMe Test-Input.csv';
    
    if (!file_exists($filePath)) {
        throw new Exception("Файл CSV не знайдено за шляхом: $filePath");
    }

    echo "          -> Збір координат з CSV..." . PHP_EOL;
    $coordinateList = [];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 0, ",", "\"", ""); 
        
        while (($data = fgetcsv($handle, 0, ",", "\"", "")) !== FALSE) {
            
            if (isset($data[1], $data[2])) {
                $coordinateList[] = [
                    'lat' => (float)$data[1], 
                    'lon' => (float)$data[2]
                ];
            }
        }
        fclose($handle);
    }

    echo "          -> Прорахунок кешу юрисдикцій..." . PHP_EOL;
    $jurisdictionService->prepareCacheForCoordinates($coordinateList);

    $orderGateway = new OrderGateway($database);
    $taxGateway = new TaxGateway($database);
    $taxCalculator = new TaxCalculatorService($taxGateway);

    $orderCreator = new OrderCreationService(
        $orderGateway, 
        $jurisdictionService, 
        $taxCalculator
    );

    $importer = new CsvImportService($orderCreator, $jurisdictionService, $pdo);

    echo "--- [3/4] Початок імпорту ---" . PHP_EOL;

    $result = $importer->import($filePath);

    echo "--- [4/4] ЗАВЕРШЕНО ---" . PHP_EOL;
    echo "Статус: " . ($result['status'] ?? 'Success') . PHP_EOL;
    echo "Успішно додано в базу: " . ($result['count'] ?? 0) . " замовлень" . PHP_EOL;

    $failedCount = $result['failed'] ?? 0;
    if ($failedCount > 0) {
        echo PHP_EOL . "--- ПОМИЛКИ ІМПОРТУ - {$failedCount} шт. ---" . PHP_EOL;
        foreach ($result['errors'] as $error) {
            echo "  " . $error . PHP_EOL;
        }
    }

    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    echo PHP_EOL . "Загальний час виконання: $executionTime сек." . PHP_EOL;

} catch (\Throwable $e) {
    echo PHP_EOL . "КРИТИЧНА ПОМИЛКА: " . $e->getMessage() . PHP_EOL;
    echo "Рядок: " . $e->getLine() . " у файлі " . $e->getFile() . PHP_EOL;
}