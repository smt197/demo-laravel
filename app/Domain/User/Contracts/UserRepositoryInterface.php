<?php

declare(strict_types=1);

namespace App\Domain\User\Contracts;

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $userId): ?User;

    public function findByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;

    public function delete(UserId $userId): void;

    /**
     * @return User[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array;

    public function count(): int;
}