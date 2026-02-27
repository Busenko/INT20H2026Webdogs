<?php

declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Data-Depth");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/private/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/private');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

set_error_handler("App\Core\ErrorHandler::handleError");
set_exception_handler("App\Core\ErrorHandler::handleException");


$database = new \App\Core\Database(
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

$authMiddleware = new \App\Middleware\AuthMiddleware();
$userData = $authMiddleware->authenticate();


$router = new \App\Core\Router($database);
$router->handleRequest($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], $userData);


