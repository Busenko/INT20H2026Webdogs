<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Security\TokenGenerator;
use App\Security\PasswordHasher;

class LoginService 
{
    /**
     * Конструктор приймає тільки AdminGateway
     */
    public function __construct(
        private $adminGateway, 
    ) {}

    public function login(string $login, string $password): ?array 
    {
        $login = trim($login);

        // 1. Отримуємо дані з бази
        $userData = $this->adminGateway->getByLogin($login);

        if (!$userData) {
            return null;
        }

        // 2. Використовуємо модель Admin
        $admin = Admin::fromArray((array)$userData);

        // 3. Перевірка пароля
        if ($admin->password && PasswordHasher::verify($password, $admin->password)) {
            
            $tokenGenerator = new TokenGenerator();
            
            // Payload містить тільки реальні дані з бази
            $payload = [
                "id"    => $admin->id,
                "login" => $admin->login
            ];

            // Повертаємо токен та публічні дані
            return [
                "token" => $tokenGenerator->generate($payload),
                "user"  => [
                    "id"    => $admin->id,
                    "login" => $admin->login
                ]
            ];
        }

        return null;
    }
}