<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final readonly class UserName
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function firstName(): string
    {
        $parts = explode(' ', $this->value);
        return $parts[0] ?? '';
    }

    public function lastName(): string
    {
        $parts = explode(' ', $this->value);
        if (count($parts) < 2) {
            return '';
        }
        return implode(' ', array_slice($parts, 1));
    }

    public function initials(): string
    {
        $words = explode(' ', $this->value);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }

        return $initials;
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
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        if (strlen($this->value) < 2) {
            throw new \InvalidArgumentException('Name must be at least 2 characters long');
        }

        if (strlen($this->value) > 255) {
            throw new \InvalidArgumentException('Name cannot exceed 255 characters');
        }

        if (!preg_match('/^[\p{L}\p{M}\s\-\'\.]+$/u', $this->value)) {
            throw new \InvalidArgumentException('Name contains invalid characters');
        }
    }
}