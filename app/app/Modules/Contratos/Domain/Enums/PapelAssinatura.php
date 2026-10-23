<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Tipo de pessoa que assina o contrato (R-3.5.1).
 */
enum PapelAssinatura: string
{
    case Imobiliaria = 'Imobiliária';
    case Locador = 'Locador';
    case Locatario = 'Locatário';
    case Fiador = 'Fiador';
}
