<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use App\Shared\Contracts\VerificadorDeSenha;
use Illuminate\Support\Facades\Hash;

final class BcryptSenhaHasher implements VerificadorDeSenha
{
    public function conferir(string $senhaEmTexto, string $hash): bool
    {
        return Hash::check($senhaEmTexto, $hash);
    }

    public function gerar(string $senhaEmTexto): string
    {
        return Hash::make($senhaEmTexto);
    }
}
