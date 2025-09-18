<?php

declare(strict_types=1);

namespace App\Application\User\Commands;

final readonly class VerifyEmailCommand
{
    public function __construct(
        public string $userId
    ) {}
}