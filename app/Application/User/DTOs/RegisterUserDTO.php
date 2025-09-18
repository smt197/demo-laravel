<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

final readonly class RegisterUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? throw new \InvalidArgumentException('Name is required'),
            email: $data['email'] ?? throw new \InvalidArgumentException('Email is required'),
            password: $data['password'] ?? throw new \InvalidArgumentException('Password is required')
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password
        ];
    }
}