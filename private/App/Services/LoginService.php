<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Security\TokenGenerator;
use App\Security\PasswordHasher;

class LoginService 
{

    public function __construct(
        private $adminGateway, 
    ) {}

    public function login(string $login, string $password): ?array 
    {
        $login = trim($login);
        $userData = $this->adminGateway->getByLogin($login);

        if (!$userData) {
            return null;
        }
        $admin = Admin::fromArray((array)$userData);

        if ($admin->password && PasswordHasher::verify($password, $admin->password)) {
            
            $tokenGenerator = new TokenGenerator();
            
            $payload = [
                "id"    => $admin->id,
                "login" => $admin->login
            ];

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