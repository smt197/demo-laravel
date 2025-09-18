<?php

declare(strict_types=1);

namespace App\Application\User\Queries;

final readonly class GetUserQuery
{
    public function __construct(
        public string $userId
    ) {}
}