<?php

declare(strict_types=1);

namespace App\Domain\User\Events;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use DateTimeImmutable;

final readonly class UserEmailVerified
{
    public function __construct(
        public UserId $userId,
        public Email $email,
        public DateTimeImmutable $verifiedAt
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId->toString(),
            'email' => $this->email->value(),
            'verifiedAt' => $this->verifiedAt->format('c'),
            'eventType' => 'user.email_verified'
        ];
    }
}