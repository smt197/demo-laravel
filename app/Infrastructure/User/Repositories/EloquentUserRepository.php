<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserName;
use App\Infrastructure\User\Models\UserEloquentModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): void
    {
        $model = UserEloquentModel::find($user->getId()->toString())
            ?? new UserEloquentModel();

        $model->fill([
            'id' => $user->getId()->toString(),
            'name' => $user->getName()->value(),
            'email' => $user->getEmail()->value(),
            'password' => $user->getPasswordHash(),
            'email_verified_at' => $user->getEmailVerifiedAt(),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ]);

        $model->save();
    }

    public function findById(UserId $userId): ?User
    {
        $model = UserEloquentModel::find($userId->toString());

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserEloquentModel::where('email', $email->value())->first();

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function existsByEmail(Email $email): bool
    {
        return UserEloquentModel::where('email', $email->value())->exists();
    }

    public function delete(UserId $userId): void
    {
        UserEloquentModel::where('id', $userId->toString())->delete();
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $models = UserEloquentModel::offset($offset)
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->modelToEntity($model))->toArray();
    }

    public function count(): int
    {
        return UserEloquentModel::count();
    }

    private function modelToEntity(UserEloquentModel $model): User
    {
        // Use reflection to create User entity from database data
        $reflection = new \ReflectionClass(User::class);
        $user = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('id')->setValue(
            $user,
            UserId::fromString($model->id)
        );
        $reflection->getProperty('name')->setValue(
            $user,
            UserName::fromString($model->name)
        );
        $reflection->getProperty('email')->setValue(
            $user,
            Email::fromString($model->email)
        );
        $reflection->getProperty('passwordHash')->setValue(
            $user,
            $model->password
        );
        $reflection->getProperty('emailVerifiedAt')->setValue(
            $user,
            $model->email_verified_at ? new \DateTimeImmutable($model->email_verified_at) : null
        );
        $reflection->getProperty('createdAt')->setValue(
            $user,
            new \DateTimeImmutable($model->created_at)
        );
        $reflection->getProperty('updatedAt')->setValue(
            $user,
            new \DateTimeImmutable($model->updated_at)
        );

        return $user;
    }
}