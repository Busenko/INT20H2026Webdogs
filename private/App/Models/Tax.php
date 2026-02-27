<?php
declare(strict_types=1);
namespace App\Models;

class Tax
{
    public function __construct(
        public float $composite_tax_rate,
        public float $state_rate = 0.0,
        public float $county_rate = 0.0,
        public float $city_rate = 0.0,
        public float $special_rates = 0.0,
        public ?string $jurisdictions = null,
        public ?string $created_at = null,
        public ?int $id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            composite_tax_rate: (float)($data['composite_tax_rate'] ?? 0.0),
 
            state_rate:         (float)($data['state_rate'] ?? 0.0),
            county_rate:        (float)($data['county_rate'] ?? 0.0),
            city_rate:          (float)($data['city_rate'] ?? 0.0),
            special_rates:      (float)($data['special_rates'] ?? 0.0),

            jurisdictions:      $data['jurisdictions'] ?? null, 
            created_at:         $data['created_at'] ?? null,
            id:                 isset($data['id']) ? (int)$data['id'] : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id'                 => $this->id,
            'composite_tax_rate' => $this->composite_tax_rate,

            'state_rate'         => $this->state_rate,
            'county_rate'        => $this->county_rate,
            'city_rate'          => $this->city_rate,
            'special_rates'      => $this->special_rates,
            'jurisdictions'      => $this->jurisdictions,
            'created_at'         => $this->created_at,
        ], fn($v) => !is_null($v)); 
    }
}