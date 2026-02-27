<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class LoginController extends BaseController
{
    public function __construct(
        protected ?object $gateway,
        protected ?object $validator,
        protected ?object $service,
        protected ?object $userData = null
    ) {
        parent::__construct($gateway, $validator, $service, $userData);
    }
    public function processRequest(string $method, ?string $id): void
    {
       
        if ($method !== "POST") {
            $this->respondMethodNotAllowed(["POST"]);
            return;
        }

        $data = $this->getJsonInput();
        
        
        $errors = $this->validator?->validate($data);
        if (!empty($errors)) {
            $this->respondUnprocessableEntity($errors);
            return;
        }

        $result = $this->service->login($data['login'] ?? '', $data['password'] ?? '');

        if (!$result) {
            $this->respondJSON(["message" => "Невірний логін або пароль"], 401);
            return;
        }

        $this->respondJSON($result);
    }
}