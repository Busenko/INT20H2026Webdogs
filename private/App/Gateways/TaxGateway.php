<?php

declare(strict_types=1);

namespace App\Gateways;

use App\Core\BaseGateway;
use App\Models\Tax;
use PDO;

class TaxGateway extends BaseGateway
{
    public function getAll(): array
    {
        $sql = "SELECT * FROM order_taxes";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => Tax::fromArray($row), $rows);
    }

    public function getUniqueJurisdictions(): array
{
    $sql = "SELECT DISTINCT jurisdictions 
            FROM order_taxes 
            WHERE jurisdictions IS NOT NULL 
            AND jurisdictions != '' 
            ORDER BY jurisdictions ASC";
            
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

    public function getById(int $id): ?Tax
    {
        $sql = "SELECT * FROM order_taxes WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Tax::fromArray($data) : null;
    }

    public function create(Tax $tax): string
    {
        $sql = "INSERT INTO order_taxes (composite_tax_rate, state_rate, county_rate, city_rate, special_rates, jurisdictions, created_at
                ) VALUES (:composite_tax_rate, :state_rate, :county_rate, :city_rate, :special_rates, :jurisdictions, :created_at)";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":composite_tax_rate", $tax->composite_tax_rate, PDO::PARAM_STR);
       
        $stmt->bindValue(":state_rate", $tax->state_rate, PDO::PARAM_STR);
        $stmt->bindValue(":county_rate", $tax->county_rate, PDO::PARAM_STR);
        $stmt->bindValue(":city_rate", $tax->city_rate, PDO::PARAM_STR);
        $stmt->bindValue(":special_rates", $tax->special_rates, PDO::PARAM_STR);
        
        $stmt->bindValue(":jurisdictions", $tax->jurisdictions, $tax->jurisdictions === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":created_at", $tax->created_at, $tax->created_at === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function delete(int $id): int
    {
        $sql = "DELETE FROM order_taxes WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    public function update(Tax $tax, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $fields = [];
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = :$key";
            }
        }

        $sql = "UPDATE order_taxes SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            if ($value === null) $type = PDO::PARAM_NULL;
            $stmt->bindValue(":$key", $value, $type);
        }
        
        $stmt->bindValue(":id", $tax->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    public function getByJurisdiction(string $name): ?Tax
{
    $sql = "SELECT * FROM order_taxes 
            WHERE jurisdictions LIKE :name 
            LIMIT 1";
            
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(":name", "%$name%", PDO::PARAM_STR);
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $data ? Tax::fromArray($data) : null;
}

public function upsert(Tax $tax): string|int
{
   
    $sqlCheck = "SELECT id FROM order_taxes WHERE jurisdictions = :jurisdictions LIMIT 1";
    $stmtCheck = $this->conn->prepare($sqlCheck);
    $stmtCheck->bindValue(":jurisdictions", $tax->jurisdictions, PDO::PARAM_STR);
    $stmtCheck->execute();
    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
       
        $updateData = [
            'composite_tax_rate' => $tax->composite_tax_rate,
            'state_rate'         => $tax->state_rate,
            'county_rate'        => $tax->county_rate,
            'city_rate'          => $tax->city_rate, 
            'special_rates'      => $tax->special_rates,
        ];
        
        $tax->id = (int)$existing['id'];
        $this->update($tax, $updateData);
        return $tax->id;
    }

 
    return $this->create($tax);
}
}