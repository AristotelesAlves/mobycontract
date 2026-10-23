<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

/**
 * Normalização de entrada comum às ações de imóvel (R-9.5).
 */
trait NormalizaCamposDoImovel
{
    protected function textoLimitado(mixed $valor): string
    {
        return is_scalar($valor) ? trim((string) $valor) : '';
    }

    protected function somenteDigitos(mixed $valor): string
    {
        return is_scalar($valor) ? (preg_replace('/\D/', '', (string) $valor) ?? '') : '';
    }

    protected function estadoNormalizado(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $texto = strtoupper(trim((string) $valor));

        return $texto === '' ? null : $texto;
    }
}
