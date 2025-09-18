<?php

declare(strict_types=1);

namespace App\Infrastructure\User\EventStore;

use Illuminate\Support\Facades\DB;

final class EventStore
{
    public function append(string $aggregateId, array $events, int $expectedVersion = -1): void
    {
        DB::transaction(function () use ($aggregateId, $events, $expectedVersion) {
            // Check current version
            $currentVersion = $this->getCurrentVersion($aggregateId);

            if ($expectedVersion !== -1 && $currentVersion !== $expectedVersion) {
                throw new \DomainException('Concurrency conflict detected');
            }

            // Store each event
            foreach ($events as $index => $event) {
                $this->storeEvent(
                    aggregateId: $aggregateId,
                    eventData: $event,
                    version: $currentVersion + $index + 1
                );
            }
        });
    }

    public function getEvents(string $aggregateId, int $fromVersion = 0): array
    {
        $records = DB::table('event_store')
            ->where('aggregate_id', $aggregateId)
            ->where('version', '>', $fromVersion)
            ->orderBy('version')
            ->get();

        return $records->map(function ($record) {
            return [
                'event_type' => $record->event_type,
                'event_data' => json_decode($record->event_data, true),
                'version' => $record->version,
                'occurred_at' => $record->occurred_at,
            ];
        })->toArray();
    }

    public function getAllEvents(int $limit = 100, int $offset = 0): array
    {
        $records = DB::table('event_store')
            ->orderBy('id')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $records->map(function ($record) {
            return [
                'aggregate_id' => $record->aggregate_id,
                'event_type' => $record->event_type,
                'event_data' => json_decode($record->event_data, true),
                'version' => $record->version,
                'occurred_at' => $record->occurred_at,
            ];
        })->toArray();
    }

    private function getCurrentVersion(string $aggregateId): int
    {
        $result = DB::table('event_store')
            ->where('aggregate_id', $aggregateId)
            ->max('version');

        return $result ?? 0;
    }

    private function storeEvent(string $aggregateId, array $eventData, int $version): void
    {
        DB::table('event_store')->insert([
            'aggregate_id' => $aggregateId,
            'event_type' => $eventData['eventType'] ?? 'unknown',
            'event_data' => json_encode($eventData),
            'version' => $version,
            'occurred_at' => $eventData['occurredAt'] ?? now()->toISOString(),
            'created_at' => now(),
        ]);
    }
}