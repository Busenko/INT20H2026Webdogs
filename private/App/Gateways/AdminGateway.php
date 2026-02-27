<?php

declare(strict_types=1);

namespace App\Gateways;

use App\Core\BaseGateway;
use App\Models\Admin;
use PDO;

class AdminGateway extends BaseGateway 
{

    public function get(string $id): ?Admin 
    {
    
        $sql = "SELECT * FROM `admins` WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
     
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Admin::fromArray($data) : null;
    }


    public function getByLogin(string $login): ?array
    {
        $sql = "SELECT * FROM `admins` WHERE login = :login LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":login", $login, PDO::PARAM_STR);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;   
    }

}