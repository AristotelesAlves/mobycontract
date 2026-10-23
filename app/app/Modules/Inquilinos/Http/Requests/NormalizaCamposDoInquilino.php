<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Requests;

/**
 * Normalização de textos recebidos nas requisições de locatários.
 */
trait NormalizaCamposDoInquilino
{
    private function textoNormalizado(mixed $valor): ?string
    {
        $texto = is_scalar($valor) ? trim((string) $valor) : '';

        return $texto === '' ? null : $texto;
    }
}
