<?php

declare(strict_types=1);

namespace App\Shared\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidaCpf implements ValidationRule
{
    private const int TAMANHO_DO_CPF = 11;

    public function validate(string $atributo, mixed $valor, Closure $falhar): void
    {
        if (! is_string($valor)) {
            $falhar('O CPF informado é inválido.');

            return;
        }

        $cpf = preg_replace('/\D/', '', $valor) ?? '';

        if (! $this->eValido($cpf)) {
            $falhar('O CPF informado é inválido.');
        }
    }

    private function eValido(string $cpf): bool
    {
        if (strlen($cpf) !== self::TAMANHO_DO_CPF) {
            return false;
        }

        if (preg_match('/^(\d)\1+$/', $cpf) === 1) {
            return false;
        }

        return $this->digitoVerificador($cpf, 9) === (int) $cpf[9]
            && $this->digitoVerificador($cpf, 10) === (int) $cpf[10];
    }

    /**
     * O dígito verificador de posição $quantidadeDeDigitos usa os $quantidadeDeDigitos
     * primeiros dígitos com pesos de $quantidadeDeDigitos+1 até 2.
     */
    private function digitoVerificador(string $cpf, int $quantidadeDeDigitos): int
    {
        $soma = 0;

        for ($posicao = 0; $posicao < $quantidadeDeDigitos; $posicao++) {
            $soma += (int) $cpf[$posicao] * ($quantidadeDeDigitos + 1 - $posicao);
        }

        $resto = $soma % 11;

        return $resto < 2 ? 0 : 11 - $resto;
    }
}
