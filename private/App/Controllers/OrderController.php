<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\OrderCreationService;
use App\Services\CsvImportService;
use App\Services\OrderFilterService;
use App\Services\JurisdictionService;
use App\Services\TaxCalculatorService;
use App\Gateways\TaxGateway;
use Exception;

class OrderController extends BaseController
{
    public function processRequest(string $method, ?string $id): void
    {
        if ($id === 'jurisdictions') {
            $this->handleJurisdictions($method);
            return;
        }

        if ($id === 'import') {
            $this->handleImport($method);
        } elseif ($id && is_numeric($id)) {
            $this->handleResource($method, $id);
        } else {
            $this->handleCollection($method);
        }
    }

    private function handleJurisdictions(string $method): void
    {
        if ($method !== 'GET') {
            $this->respondMethodNotAllowed(["GET"]);
            return;
        }

        try {
            $database = $this->gateway->getDatabase();
            $taxGateway = new TaxGateway($database);
            
            $data = $taxGateway->getUniqueJurisdictions();
            
            header("Content-Type: application/json");
            echo json_encode($data);
        } catch (Exception $e) {
            $this->respondJSON(["error" => $e->getMessage()], 500);
        }
    }

    private function handleImport(string $method): void
    {
        if ($method !== 'POST') {
            $this->respondMethodNotAllowed(["POST"]);
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->respondUnprocessableEntity(["file" => "Валідний CSV файл обов'язковий"]);
        }

        try {
            $database = $this->gateway->getDatabase();
            $pdo = $database->getConnection();

            $jurisdictionService = new JurisdictionService();
            $taxGateway = new TaxGateway($database);
            $taxCalculator = new TaxCalculatorService($taxGateway);
            
            $orderCreator = new OrderCreationService(
                $this->gateway, 
                $jurisdictionService, 
                $taxCalculator
            );
            
            $importer = new CsvImportService(
                $orderCreator,
                $jurisdictionService,
                $pdo
            );

        $result = $importer->import($_FILES['file']['tmp_name']);
            $this->respondJSON([
                "message" => "Імпорт завершено",
                "status"  => $result['status'],
                "data"    => [
                    "imported_count" => $result['count'],
                    "failed_count"   => $result['failed'],
                    "errors"         => $result['errors']
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->respondJSON(["error" => $e->getMessage()], 500);
        }
    }

    private function handleCollection(string $method): void
    {
        switch ($method) {
            case "GET":
                $filterService = new OrderFilterService($this->gateway);
                $data = $filterService->getFilteredOrders($_GET);
                $this->respondJSON($data);
                break;

            case "POST":
                $data = $this->getJsonInput();
                $errors = $this->validator->validate($data);
                
                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                }

                try {
                    $database = $this->gateway->getDatabase();
                    $jurisdictionService = new JurisdictionService();
                    $taxGateway = new TaxGateway($database);
                    $taxCalculator = new TaxCalculatorService($taxGateway);
                    
                    $creator = new OrderCreationService(
                        $this->gateway, 
                        $jurisdictionService, 
                        $taxCalculator
                    );

                    $id = $creator->createOrder($data);
                    $this->respondCreated((string)$id);
                } catch (Exception $e) {
                    $this->respondUnprocessableEntity(["error" => $e->getMessage()]);
                }
                break;

            default:
                $this->respondMethodNotAllowed(["GET", "POST"]);
        }
    }

    private function handleResource(string $method, string $id): void
    {
        $order = $this->gateway->getById((int)$id);

        if (!$order) {
            $this->respondNotFound("Order", $id);
        }

        if ($method === "GET") {
            $this->respondJSON($order->toArray());
        } elseif ($method === "DELETE") {
            $this->gateway->delete((int)$id);
            $this->respondJSON(["message" => "Замовлення $id видалено"]);
        } else {
            $this->respondMethodNotAllowed(["GET", "DELETE"]);
        }
    }
}