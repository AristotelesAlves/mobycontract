<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Events;

use App\Shared\Contracts\DomainEventDispatcher;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class EventoDeDominioDispatcher implements DomainEventDispatcher
{
    public function __construct(
        private Dispatcher $eventos,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function publicar(string $evento, array $payload): void
    {
        $this->eventos->dispatch($evento, $payload);
    }
}
