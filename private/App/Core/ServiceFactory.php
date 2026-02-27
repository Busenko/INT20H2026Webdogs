<?php
namespace App\Core;

class ServiceFactory {
    public function __construct(private Database $database) {}

    public function create(string $resource): ?object {
        
        $modelName = rtrim(ucfirst($resource), 's');
        $serviceClass = "App\\Services\\" . $modelName . "Service";
        
        if (!class_exists($serviceClass)) return null;

        return match ($resource) {
            'login' => new \App\Services\LoginService(
                new \App\Gateways\AdminGateway($this->database)
            ),
            'orders' => new \App\Services\OrderService(
                new \App\Gateways\OrderGateway($this->database)
            ),
            
            default => new $serviceClass($this->createGateway($modelName))
        };
    }

    private function createGateway(string $modelName): object {
        $class = "App\\Gateways\\" . $modelName . "Gateway";
        return new $class($this->database);
    }
}