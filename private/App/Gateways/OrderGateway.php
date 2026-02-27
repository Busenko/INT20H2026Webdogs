<?php

declare(strict_types=1);

namespace App\Gateways;

use App\Core\BaseGateway;
use App\Models\Order;
use PDO;

class OrderGateway extends BaseGateway
{
    public function getAll(): array
    {
        $sql = "SELECT * FROM orders";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => Order::fromArray($row), $rows);
    }

    public function getById(int $id): ?Order
    {
        $sql = "SELECT * FROM orders WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Order::fromArray($data) : null;
    }

    public function create(Order $order): string
    {
        $sql = "INSERT INTO orders (latitude, longitude, subtotal, tax_amount, total_amount, id_tax, created_at)
                VALUES (:latitude, :longitude, :subtotal, :tax_amount, :total_amount, :id_tax, :created_at)";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":latitude", $order->latitude, PDO::PARAM_STR);
        $stmt->bindValue(":longitude", $order->longitude, PDO::PARAM_STR);
        $stmt->bindValue(":subtotal", $order->subtotal, PDO::PARAM_STR);
        $stmt->bindValue(":tax_amount", $order->tax_amount, $order->tax_amount === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":total_amount", $order->total_amount, $order->total_amount === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":id_tax", $order->id_tax, $order->id_tax === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":created_at", $order->created_at, $order->created_at === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function delete(int $id): int
    {
        $sql = "DELETE FROM orders WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    public function update(Order $order, array $data): int
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

        $sql = "UPDATE orders SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            if ($value === null) $type = PDO::PARAM_NULL;
            $stmt->bindValue(":$key", $value, $type);
        }
        
        $stmt->bindValue(":id", $order->id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
public function getListWithFilters(array $filters): array
{
    $limit = (int)$filters['limit'];
    $page = (int)$filters['page'];
    $offset = ($page - 1) * $limit;
    
    $sortDir = (isset($filters['sort']) && strtoupper($filters['sort']) === 'ASC') ? 'ASC' : 'DESC';
    
    $where = [];
    $params = [];

    // Фільтрація за ID замовлення
    if (!empty($filters['id'])) {
        $where[] = "o.id = :id";
        $params[':id'] = (int)$filters['id'];
    }

    // Фільтрація за юрисдикцією (округом)
    if (!empty($filters['county'])) {
        $where[] = "t.jurisdictions = :county";
        $params[':county'] = $filters['county'];
    }

    // Фільтрація за координатами з округленням для точності
    if (isset($filters['lat']) && is_numeric($filters['lat'])) {
        $where[] = "ROUND(o.latitude, 4) = ROUND(:lat, 4)";
        $params[':lat'] = $filters['lat']; 
    }
    
    if (isset($filters['lon']) && is_numeric($filters['lon'])) {
        $where[] = "ROUND(o.longitude, 4) = ROUND(:lon, 4)";
        $params[':lon'] = $filters['lon']; 
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // ОНОВЛЕНО: Явно вибираємо o.* та деталі податків, щоб уникнути конфліктів ID
    $sql = "SELECT 
                o.*, 
                t.jurisdictions as county_name,
                t.composite_tax_rate,
                t.state_rate,
                t.county_rate,
                t.city_rate,
                t.special_rates
            FROM orders o
            LEFT JOIN order_taxes t ON o.id_tax = t.id
            $whereSql
            ORDER BY o.created_at $sortDir, o.id DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $this->conn->prepare($sql);
    
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Підрахунок загальної кількості для пагінації
    $countSql = "SELECT COUNT(*) FROM orders o 
                 LEFT JOIN order_taxes t ON o.id_tax = t.id 
                 $whereSql";
                 
    $countStmt = $this->conn->prepare($countSql);
    
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    return [
        'data' => $orders,
        'meta' => [
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => (int)ceil($total / $limit)
        ]
    ];
}
}