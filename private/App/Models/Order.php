<?php
declare(strict_types=1);
namespace App\Models;

class Order
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public float $subtotal,
        public ?float $tax_amount = null,
        public ?float $total_amount = null,
        public ?int $id_tax = null,
        public ?string $created_at = null,
        public ?int $id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            latitude:     (float)($data['latitude'] ?? 0.0), 
            longitude:    (float)($data['longitude'] ?? 0.0),
            subtotal:     (float)($data['subtotal'] ?? 0.0),
            tax_amount:   isset($data['tax_amount']) ? (float)$data['tax_amount'] : null,
            total_amount: isset($data['total_amount']) ? (float)$data['total_amount'] : null,
            id_tax:       isset($data['id_tax']) ? (int)$data['id_tax'] : null,
            created_at:   $data['created_at'] ?? null,
            id:           isset($data['id']) ? (int)$data['id'] : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id'           => $this->id,
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'subtotal'     => $this->subtotal,
            'tax_amount'   => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'id_tax'       => $this->id_tax,
            'created_at'   => $this->created_at,
        ], fn($v) => !is_null($v)); 
    }
}