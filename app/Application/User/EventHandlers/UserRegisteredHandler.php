<?php

declare(strict_types=1);

namespace App\Application\User\EventHandlers;

use App\Domain\User\Events\UserRegistered;
use App\Infrastructure\User\ReadModels\UserReadModel;
use App\Infrastructure\User\EventStore\EventStore;
use Illuminate\Support\Facades\Log;

final class UserRegisteredHandler
{
    public function __construct(
        private EventStore $eventStore
    ) {}

    public function handle(UserRegistered $event): void
    {
        try {
            // Store event in event store
            $this->eventStore->append(
                aggregateId: $event->userId->toString(),
                events: [$event->toArray()]
            );

            // Update read model
            UserReadModel::updateOrCreate(
                ['id' => $event->userId->toString()],
                [
                    'name' => $event->name->value(),
                    'email' => $event->email->value(),
                    'email_verified' => false,
                    'registration_date' => $event->occurredAt,
                    'status' => 'active',
                    'created_at' => $event->occurredAt,
                    'updated_at' => $event->occurredAt,
                ]
            );

            Log::info('User registered event processed', [
                'user_id' => $event->userId->toString(),
                'email' => $event->email->value(),
                'event_type' => 'user.registered'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process user registered event', [
                'user_id' => $event->userId->toString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}