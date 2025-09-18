<?php

declare(strict_types=1);

namespace App\Application\User\Handlers;

use App\Application\User\Queries\GetUserQuery;
use App\Application\User\DTOs\UserResponseDTO;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

final class GetUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetUserQuery $query): ?UserResponseDTO
    {
        $userId = UserId::fromString($query->userId);

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return null;
        }

        return UserResponseDTO::fromEntity($user);
    }
}