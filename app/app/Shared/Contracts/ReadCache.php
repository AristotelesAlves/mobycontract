<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use Closure;

interface ReadCache
{
    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $origem
     * @return TValue
     */
    public function lembrar(string $chave, int $segundosParaViver, Closure $origem): mixed;

    public function versionarPara(string $chaveDeVersao): int;

    public function avancarVersao(string $chaveDeVersao): int;

    public function disponivel(): bool;
}
