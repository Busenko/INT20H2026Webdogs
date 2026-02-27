<?php

declare(strict_types=1);

namespace App\Core;

abstract class BaseController
{
    public function __construct(
        protected ?object $gateway = null,
        protected ?object $validator = null,
        protected ?object $service = null,
        protected ?object $userData = null
    ) {}

    abstract public function processRequest(string $method, ?string $id): void;

    protected function getJsonInput(): array
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->respondJSON(["message" => "Невірний формат JSON"], 400);
            exit;
        }

        return $this->trimArray((array)$data);
    }

    private function trimArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->trimArray($value);
            }
        }
        return $data;
    }

    protected function respondJSON(mixed $data, int $code = 200): void
    {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    protected function respondNotFound(string $resource, string $id = ''): void
    {
        $message = $id ? "$resource з ID $id не знайдено" : "Ресурс $resource не знайдено";
        $this->respondJSON(["message" => $message], 404);
    }

    protected function respondUnprocessableEntity(array $errors): void
    {
        $this->respondJSON([
            "message" => "Помилка валідації",
            "errors" => $errors
        ], 422);
    }

    protected function respondMethodNotAllowed(array $allowedMethods): void
    {
        header("Allow: " . implode(", ", $allowedMethods));
        $this->respondJSON([
            "message" => "Метод не дозволений. Доступні: " . implode(", ", $allowedMethods)
        ], 405);
    }

    protected function respondCreated(string $id): void
    {
        $this->respondJSON([
            "message" => "Ресурс створено",
            "id" => $id
        ], 201);
    }
}