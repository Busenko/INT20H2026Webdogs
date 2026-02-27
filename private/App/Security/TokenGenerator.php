<?php
namespace App\Security;

use Firebase\JWT\JWT;

class TokenGenerator {
    public function generate(array $userData): string {
        $payload = [
            "iss" => "dorm-system",
            "iat" => time(),
            "exp" => time() + 3600,
            "data" => $userData
        ];
        return JWT::encode($payload, $_ENV['JWT_SECRET'], $_ENV['JWT_ALGO']);
    }
}
