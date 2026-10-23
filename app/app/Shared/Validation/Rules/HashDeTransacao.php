<?php

declare(strict_types=1);

namespace App\Shared\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Garante o formato hexadecimal de 32 bytes usado por transações EVM. */
final class HashDeTransacao implements ValidationRule
{
    private const string PADRAO_HASH_EVM = '/^0x[a-fA-F0-9]{64}$/D';

    public function validate(string $atributo, mixed $valor, Closure $falhar): void
    {
        if (! is_string($valor) || preg_match(self::PADRAO_HASH_EVM, $valor) !== 1) {
            $falhar('O hash da transação deve conter 0x seguido de 64 caracteres hexadecimais.');
        }
    }
}
