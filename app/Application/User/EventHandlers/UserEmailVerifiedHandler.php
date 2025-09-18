<?php

declare(strict_types=1);

namespace App\Application\User\EventHandlers;

use App\Domain\User\Events\UserEmailVerified;
use App\Infrastructure\User\ReadModels\UserReadModel;
use App\Infrastructure\User\EventStore\EventStore;
use Illuminate\Support\Facades\Log;

final class UserEmailVerifiedHandler
{
    public function __construct(
        private EventStore $eventStore
    ) {}

    public function handle(UserEmailVerified $event): void
    {
        try {
            // Store event in event store
            $this->eventStore->append(
                aggregateId: $event->userId->toString(),
                events: [$event->toArray()]
            );

            // Update read model
            $readModel = UserReadModel::find($event->userId->toString());
            if ($readModel) {
                $readModel->update([
                    'email_verified' => true,
                    'updated_at' => $event->verifiedAt,
                ]);
            }

            Log::info('User email verified event processed', [
                'user_id' => $event->userId->toString(),
                'email' => $event->email->value(),
                'verified_at' => $event->verifiedAt->format('c'),
                'event_type' => 'user.email_verified'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process user email verified event', [
                'user_id' => $event->userId->toString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}