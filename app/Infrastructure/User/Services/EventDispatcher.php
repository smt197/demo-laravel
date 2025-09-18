<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Services;

use Illuminate\Contracts\Events\Dispatcher;

final class EventDispatcher
{
    public function __construct(
        private Dispatcher $laravelDispatcher
    ) {}

    public function dispatchEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->laravelDispatcher->dispatch($event);
        }
    }

    public function dispatch(object $event): void
    {
        $this->laravelDispatcher->dispatch($event);
    }
}