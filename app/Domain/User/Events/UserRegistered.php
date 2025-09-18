<?php

declare(strict_types=1);

namespace App\Domain\User\Events;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserName;
use DateTimeImmutable;

final readonly class UserRegistered
{
    public function __construct(
        public UserId $userId,
        public Email $email,
        public UserName $name,
        public DateTimeImmutable $occurredAt
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId->toString(),
            'email' => $this->email->value(),
            'name' => $this->name->value(),
            'occurredAt' => $this->occurredAt->format('c'),
            'eventType' => 'user.registered'
        ];
    }
}