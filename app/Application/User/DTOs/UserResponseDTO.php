<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

use App\Domain\User\Entities\User;

final readonly class UserResponseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public ?string $emailVerifiedAt,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->toString(),
            name: $user->getName()->value(),
            email: $user->getEmail()->value(),
            emailVerifiedAt: $user->getEmailVerifiedAt()?->format('c'),
            createdAt: $user->getCreatedAt()->format('c'),
            updatedAt: $user->getUpdatedAt()->format('c')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}