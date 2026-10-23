<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Requests;

/**
 * Normalização de entrada comum às ações de lead.
 */
trait NormalizaCamposDoLead
{
    protected function textoLimitado(mixed $valor): string
    {
        return is_scalar($valor) ? trim((string) $valor) : '';
    }

    protected function textoOpcionalLimitado(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }
}
