<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface CacheKeyFactory
{
    /**
     * @param  array<string, scalar|null>  $criterios
     */
    public function paraConsulta(
        string $modulo,
        string $recurso,
        string $operacao,
        array $criterios = [],
    ): string;

    public function paraVersao(string $modulo, string $recurso): string;

    /**
     * @return list<string>
     */
    public function prefixosDoRecurso(string $modulo, string $recurso): array;
}
