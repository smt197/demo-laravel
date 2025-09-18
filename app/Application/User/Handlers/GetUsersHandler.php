<?php

declare(strict_types=1);

namespace App\Application\User\Handlers;

use App\Application\User\Queries\GetUsersQuery;
use App\Application\User\DTOs\UserResponseDTO;
use App\Domain\User\Contracts\UserRepositoryInterface;

final class GetUsersHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * @return UserResponseDTO[]
     */
    public function handle(GetUsersQuery $query): array
    {
        $users = $this->userRepository->findAll($query->limit, $query->offset);

        return array_map(
            fn($user) => UserResponseDTO::fromEntity($user),
            $users
        );
    }
}