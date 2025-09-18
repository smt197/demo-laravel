<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use App\Domain\User\Contracts\UserRepositoryInterface;

final class UserDomainService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function createUser(
        UserName $name,
        Email $email,
        string $passwordHash
    ): User {
        if ($this->userRepository->existsByEmail($email)) {
            throw new \DomainException("User with email {$email} already exists");
        }

        $userId = UserId::generate();

        return User::register(
            id: $userId,
            name: $name,
            email: $email,
            passwordHash: $passwordHash
        );
    }

    public function changeUserEmail(UserId $userId, Email $newEmail): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \DomainException("User not found");
        }

        if ($this->userRepository->existsByEmail($newEmail)) {
            throw new \DomainException("Email {$newEmail} is already in use");
        }

        $user->changeEmail($newEmail);

        return $user;
    }

    public function isEmailAvailable(Email $email): bool
    {
        return !$this->userRepository->existsByEmail($email);
    }

    public function generateUniqueUserId(): UserId
    {
        do {
            $userId = UserId::generate();
        } while ($this->userRepository->findById($userId) !== null);

        return $userId;
    }
}