<?php

declare(strict_types=1);

namespace App\Application\User\Handlers;

use App\Application\User\Commands\VerifyEmailCommand;
use App\Application\User\DTOs\UserResponseDTO;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\User\Services\EventDispatcher;

final class VerifyEmailHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventDispatcher $eventDispatcher
    ) {}

    public function handle(VerifyEmailCommand $command): UserResponseDTO
    {
        $userId = UserId::fromString($command->userId);

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \DomainException('User not found');
        }

        $user->verifyEmail();
        $this->userRepository->save($user);

        // Dispatch domain events
        $this->eventDispatcher->dispatchEvents($user->getDomainEvents());
        $user->clearDomainEvents();

        return UserResponseDTO::fromEntity($user);
    }
}