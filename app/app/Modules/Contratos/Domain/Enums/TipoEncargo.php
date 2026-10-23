<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Tipo de encargo lançado sobre uma parcela (R-4.7.1, R-6.3).
 */
enum TipoEncargo: string
{
    case Multa = 'multa';
    case Juros = 'juros';
    case Taxa = 'taxa';
    case Desconto = 'desconto';
}
