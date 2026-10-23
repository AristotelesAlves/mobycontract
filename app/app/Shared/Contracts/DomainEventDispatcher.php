<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface DomainEventDispatcher
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function publicar(string $evento, array $payload): void;
}
