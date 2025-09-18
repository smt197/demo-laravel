<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Providers;

use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Infrastructure\User\Repositories\EloquentUserRepository;
use App\Infrastructure\User\Services\EventDispatcher;
use App\Application\User\Handlers\RegisterUserHandler;
use App\Application\User\Handlers\VerifyEmailHandler;
use App\Application\User\Handlers\GetUserHandler;
use App\Application\User\Handlers\GetUsersHandler;
use App\Domain\User\Services\UserDomainService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;

final class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository binding
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        // Event dispatcher
        $this->app->bind(EventDispatcher::class, function ($app) {
            return new EventDispatcher($app->make(Dispatcher::class));
        });

        // Domain service
        $this->app->bind(UserDomainService::class, function ($app) {
            return new UserDomainService(
                $app->make(UserRepositoryInterface::class)
            );
        });

        // Command handlers
        $this->app->bind(RegisterUserHandler::class, function ($app) {
            return new RegisterUserHandler(
                $app->make(UserRepositoryInterface::class),
                $app->make(UserDomainService::class),
                $app->make(EventDispatcher::class)
            );
        });

        $this->app->bind(VerifyEmailHandler::class, function ($app) {
            return new VerifyEmailHandler(
                $app->make(UserRepositoryInterface::class),
                $app->make(EventDispatcher::class)
            );
        });

        // Query handlers
        $this->app->bind(GetUserHandler::class, function ($app) {
            return new GetUserHandler(
                $app->make(UserRepositoryInterface::class)
            );
        });

        $this->app->bind(GetUsersHandler::class, function ($app) {
            return new GetUsersHandler(
                $app->make(UserRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        // Register event listeners for domain events
        $this->registerEventListeners();
    }

    private function registerEventListeners(): void
    {
        // Register domain event listeners
        $this->app['events']->listen(
            \App\Domain\User\Events\UserRegistered::class,
            \App\Application\User\EventHandlers\UserRegisteredHandler::class
        );

        $this->app['events']->listen(
            \App\Domain\User\Events\UserEmailVerified::class,
            \App\Application\User\EventHandlers\UserEmailVerifiedHandler::class
        );
    }
}