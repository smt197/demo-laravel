<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserName;
use App\Domain\User\Events\UserRegistered;
use App\Domain\User\Events\UserEmailVerified;
use DateTimeImmutable;

final class User
{
    private array $domainEvents = [];

    private function __construct(
        private UserId $id,
        private UserName $name,
        private Email $email,
        private string $passwordHash,
        private ?DateTimeImmutable $emailVerifiedAt = null,
        private DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private DateTimeImmutable $updatedAt = new DateTimeImmutable()
    ) {}

    public static function register(
        UserId $id,
        UserName $name,
        Email $email,
        string $passwordHash
    ): self {
        $user = new self(
            id: $id,
            name: $name,
            email: $email,
            passwordHash: $passwordHash,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );

        $user->recordDomainEvent(new UserRegistered(
            userId: $id,
            email: $email,
            name: $name,
            occurredAt: new DateTimeImmutable()
        ));

        return $user;
    }

    public function verifyEmail(): void
    {
        if ($this->emailVerifiedAt !== null) {
            throw new \DomainException('Email is already verified');
        }

        $this->emailVerifiedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->recordDomainEvent(new UserEmailVerified(
            userId: $this->id,
            email: $this->email,
            verifiedAt: $this->emailVerifiedAt
        ));
    }

    public function changeName(UserName $newName): void
    {
        if ($this->name->equals($newName)) {
            return;
        }

        $this->name = $newName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeEmail(Email $newEmail): void
    {
        if ($this->email->equals($newEmail)) {
            return;
        }

        $this->email = $newEmail;
        $this->emailVerifiedAt = null; // Reset verification when email changes
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePassword(string $newPasswordHash): void
    {
        $this->passwordHash = $newPasswordHash;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    // Getters
    public function getId(): UserId
    {
        return $this->id;
    }

    public function getName(): UserName
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Domain Events
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    private function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}