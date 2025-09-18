<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class UserId
{
    private function __construct(
        private UuidInterface $value
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }

        return new self(Uuid::fromString($value));
    }

    public static function fromInt(int $value): self
    {
        // Pour compatibilité avec les IDs auto-increment existants
        $uuid = Uuid::fromInteger($value);
        return new self($uuid);
    }

    public function value(): UuidInterface
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}