<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware {
    public function authenticate(): ?object {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }
        
        $token = $matches[1];

        try {
            return JWT::decode($token, new Key($_ENV['JWT_SECRET'], $_ENV['JWT_ALGO']));
            
        } catch (Exception $e) {
         
            return null;
        }
    }
}