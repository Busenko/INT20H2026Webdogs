<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class BaseGateway
{
    protected PDO $conn;

    public function __construct(protected Database $database)
    {
        $this->conn = $database->getConnection();
    }

    // Додай цей метод
    public function getConnection(): PDO
    {
        return $this->conn;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }
}