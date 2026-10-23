<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface VerificadorDeSenha
{
    public function conferir(string $senhaEmTexto, string $hash): bool;

    public function gerar(string $senhaEmTexto): string;
}
