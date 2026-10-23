<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Domain;

use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;

/**
 * Valida CPF (11 dígitos) e CNPJ (14 dígitos) com dígito verificador (R-4.4.5, R-4.4.6, R-4.4.19).
 */
final class ValidadorDeDocumento
{
    private const int TAMANHO_CPF = 11;

    private const int TAMANHO_CNPJ = 14;

    public static function somenteDigitos(string $documento): string
    {
        return preg_replace('/\D/', '', $documento) ?? '';
    }

    public static function eValido(string $documento, TipoPessoa $tipo): bool
    {
        $digitos = self::somenteDigitos($documento);

        return match ($tipo) {
            TipoPessoa::Pf => self::cpfValido($digitos),
            TipoPessoa::Pj => self::cnpjValido($digitos),
        };
    }

    private static function cpfValido(string $cpf): bool
    {
        if (strlen($cpf) !== self::TAMANHO_CPF || preg_match('/^(\d)\1+$/', $cpf) === 1) {
            return false;
        }

        return self::digitoVerificador($cpf, 9) === (int) $cpf[9]
            && self::digitoVerificador($cpf, 10) === (int) $cpf[10];
    }

    private static function cnpjValido(string $cnpj): bool
    {
        if (strlen($cnpj) !== self::TAMANHO_CNPJ || preg_match('/^(\d)\1+$/', $cnpj) === 1) {
            return false;
        }

        return self::digitoVerificadorCnpj($cnpj, 12) === (int) $cnpj[12]
            && self::digitoVerificadorCnpj($cnpj, 13) === (int) $cnpj[13];
    }

    private static function digitoVerificador(string $cpf, int $quantidadeDeDigitos): int
    {
        $soma = 0;

        for ($posicao = 0; $posicao < $quantidadeDeDigitos; $posicao++) {
            $soma += (int) $cpf[$posicao] * ($quantidadeDeDigitos + 1 - $posicao);
        }

        $resto = ($soma * 10) % 11;

        return $resto === 10 ? 0 : $resto;
    }

    private static function digitoVerificadorCnpj(string $cnpj, int $quantidadeDeDigitos): int
    {
        $pesos = $quantidadeDeDigitos === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $soma = 0;

        for ($posicao = 0; $posicao < $quantidadeDeDigitos; $posicao++) {
            $soma += (int) $cnpj[$posicao] * $pesos[$posicao];
        }

        $resto = $soma % 11;

        return $resto < 2 ? 0 : 11 - $resto;
    }
}
