<?php

declare(strict_types=1);

namespace App\Shared\Application\Support;

/**
 * Converte valores do array já validado para tipos primitivos sem confiar em `mixed` (R-8.2).
 * Não executa regra de negócio: apenas estreita o tipo antes do cast.
 */
final class DadosDeEntrada
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public static function texto(array $dados, string $chave): string
    {
        $valor = $dados[$chave] ?? '';

        return is_scalar($valor) ? (string) $valor : '';
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function textoOpcional(array $dados, string $chave): ?string
    {
        $valor = $dados[$chave] ?? null;

        if (! is_scalar($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function inteiro(array $dados, string $chave, int $padrao = 0): int
    {
        $valor = $dados[$chave] ?? $padrao;

        return is_numeric($valor) ? (int) $valor : $padrao;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function booleanoOpcional(array $dados, string $chave): ?bool
    {
        if (! array_key_exists($chave, $dados) || $dados[$chave] === null) {
            return null;
        }

        return filter_var($dados[$chave], FILTER_VALIDATE_BOOL);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function existe(array $dados, string $chave): bool
    {
        return array_key_exists($chave, $dados);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function decimal(array $dados, string $chave, float $padrao = 0.0): float
    {
        $valor = $dados[$chave] ?? $padrao;

        return is_numeric($valor) ? (float) $valor : $padrao;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function decimalOpcional(array $dados, string $chave): ?float
    {
        $valor = $dados[$chave] ?? null;

        return is_numeric($valor) ? (float) $valor : null;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function booleano(array $dados, string $chave, bool $padrao = false): bool
    {
        if (! array_key_exists($chave, $dados) || $dados[$chave] === null) {
            return $padrao;
        }

        return filter_var($dados[$chave], FILTER_VALIDATE_BOOL);
    }
}
