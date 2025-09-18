<?php

declare(strict_types=1);

namespace App\Application\User\Commands;

use App\Application\User\DTOs\RegisterUserDTO;

final readonly class RegisterUserCommand
{
    public function __construct(
        public RegisterUserDTO $userData
    ) {}
}