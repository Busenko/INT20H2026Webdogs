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

use App\Services\TaxSeedService;
use App\Gateways\TaxGateway;
use App\Validators\TaxValidator;
use App\Core\Database;

try {
    header('Content-Type: text/plain; charset=utf-8');

    echo "--- Ініціалізація бази даних ---" . PHP_EOL;

    $database = new Database(
        $_ENV['DB_HOST'] ?? "MySQL-8.4", 
        $_ENV['DB_NAME'] ?? "order_db", 
        $_ENV['DB_USER'] ?? "root", 
        $_ENV['DB_PASS'] ?? ""
    );

    echo "--- Підготовка сервісів ---" . PHP_EOL;

    $gateway = new TaxGateway($database); 
    $validator = new TaxValidator(); 
    $service = new TaxSeedService($gateway, $validator);

    echo "--- Початок синхронізації з JSON ---" . PHP_EOL;
    
    $result = $service->syncOfficialTaxData();

    echo "--- УСПІШНО ---" . PHP_EOL;
    echo "Імпортовано рядків: " . $result['imported'] . PHP_EOL;
    echo "Джерело: " . $result['source'] . PHP_EOL;

} catch (\Throwable $e) {
    echo "КРИТИЧНА ПОМИЛКА: " . $e->getMessage() . PHP_EOL;
    echo "Файл: " . $e->getFile() . " (рядок " . $e->getLine() . ")" . PHP_EOL;
}