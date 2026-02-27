<?php
declare(strict_types=1);

namespace App\Models;

class Admin
{

    public function __construct(
     
        public string $login,
        public ?string $password,
        public ?int $id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            login:         $data['login'] ?? '',
            password:      $data['password'],
            id:            isset($data['id']) ? (int)$data['id'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'login'         => $this->login,
            'password'      => $this->password,
        ];
    }
}