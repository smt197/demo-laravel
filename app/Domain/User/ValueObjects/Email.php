<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final readonly class Email
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $value): self
    {
        return new self(trim(strtolower($value)));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function localPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: {$this->value}");
        }

        if (strlen($this->value) > 254) {
            throw new \InvalidArgumentException('Email address is too long');
        }

        if (strlen($this->localPart()) > 64) {
            throw new \InvalidArgumentException('Email local part is too long');
        }
    }
}