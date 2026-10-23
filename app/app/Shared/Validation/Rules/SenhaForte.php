<?php

declare(strict_types=1);

namespace App\Shared\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class SenhaForte implements ValidationRule
{
    private const int TAMANHO_MINIMO = 8;

    public function validate(string $atributo, mixed $valor, Closure $falhar): void
    {
        if (! is_string($valor)) {
            $falhar('A senha informada é inválida.');

            return;
        }

        if (! $this->possuiTamanhoMinimo($valor)) {
            $falhar('A senha deve ter ao menos '.self::TAMANHO_MINIMO.' caracteres.');

            return;
        }

        if (! $this->possuiLetra($valor)) {
            $falhar('A senha deve conter ao menos uma letra.');

            return;
        }

        if (! $this->possuiNumero($valor)) {
            $falhar('A senha deve conter ao menos um número.');
        }
    }

    private function possuiTamanhoMinimo(string $senha): bool
    {
        return mb_strlen($senha) >= self::TAMANHO_MINIMO;
    }

    private function possuiLetra(string $senha): bool
    {
        return preg_match('/\p{L}/u', $senha) === 1;
    }

    private function possuiNumero(string $senha): bool
    {
        return preg_match('/\d/', $senha) === 1;
    }
}
