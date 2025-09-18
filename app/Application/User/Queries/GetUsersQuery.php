<?php

declare(strict_types=1);

namespace App\Application\User\Queries;

final readonly class GetUsersQuery
{
    public function __construct(
        public int $limit = 50,
        public int $offset = 0,
        public ?string $searchTerm = null
    ) {}
}