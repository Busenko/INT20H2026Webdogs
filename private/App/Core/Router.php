<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $publicResources = ["login"];

    private array $routes = [
        "login" => \App\Controllers\LoginController::class,
        "orders" => \App\Controllers\OrderController::class,   
    ];

    public function __construct(private Database $database) {}

public function handleRequest(string $uri, string $method, ?object $userData = null): void
{
  header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Data-Depth");
header("Content-Type: application/json; charset=UTF-8");

    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $path = parse_url($uri, PHP_URL_PATH);
    $parts = explode("/", trim($path, "/"));
    
    $resource = $parts[0] ?? null;
    $id = $parts[1] ?? null;

    if (!$resource || !array_key_exists($resource, $this->routes)) {
        $this->respondNotFound();
        return;
    }

    if (!in_array($resource, $this->publicResources) && $userData === null) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: Please log in"]);
        return;
    }


    $serviceFactory = new ServiceFactory($this->database);
    $service = $serviceFactory->create($resource);

    $modelName = rtrim(ucfirst($resource), 's'); 
    
    $gatewayClass = "App\\Gateways\\" . $modelName . "Gateway";
    $gateway = class_exists($gatewayClass) ? new $gatewayClass($this->database) : null;

    $validatorClass = "App\\Validators\\" . $modelName . "Validator";
    $validator = class_exists($validatorClass) ? new $validatorClass() : null;

    $controllerClass = $this->routes[$resource];
    $controller = new $controllerClass($gateway, $validator, $service, $userData);
    
    $controller->processRequest($method, $id);
}

    private function respondNotFound(): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Resource not found"]);
    }
}