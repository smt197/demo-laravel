<?php

declare(strict_types=1);

namespace App\Application\User\Handlers;

use App\Application\User\Commands\RegisterUserCommand;
use App\Application\User\DTOs\UserResponseDTO;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\Services\UserDomainService;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserName;
use App\Infrastructure\User\Services\EventDispatcher;
use Illuminate\Support\Facades\Hash;

final class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserDomainService $userDomainService,
        private EventDispatcher $eventDispatcher
    ) {}

    public function handle(RegisterUserCommand $command): UserResponseDTO
    {
        $userData = $command->userData;

        $name = UserName::fromString($userData->name);
        $email = Email::fromString($userData->email);
        $passwordHash = Hash::make($userData->password);

        $user = $this->userDomainService->createUser(
            name: $name,
            email: $email,
            passwordHash: $passwordHash
        );

        $this->userRepository->save($user);

        // Dispatch domain events
        $this->eventDispatcher->dispatchEvents($user->getDomainEvents());
        $user->clearDomainEvents();

        return UserResponseDTO::fromEntity($user);
    }
}