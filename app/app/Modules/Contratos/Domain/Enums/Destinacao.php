<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Destinação do imóvel no contrato (R-12.8.5.2).
 */
enum Destinacao: string
{
    case Residencial = 'Residencial';
    case Comercial = 'Comercial';
}
