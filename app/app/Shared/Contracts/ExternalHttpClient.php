<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface ExternalHttpClient
{
    /**
     * @param  array<string, string>  $headers
     * @return array{status: int, body: array<string, mixed>}
     */
    public function get(string $url, array $headers = [], int $timeout = 5): array;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @return array{status: int, body: array<string, mixed>}
     */
    public function post(string $url, array $payload, array $headers = [], int $timeout = 10): array;
}
