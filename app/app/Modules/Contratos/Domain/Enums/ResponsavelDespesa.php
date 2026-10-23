<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Responsável por uma despesa do imóvel (R-12.8.5.3).
 */
enum ResponsavelDespesa: string
{
    case Inquilino = 'Inquilino';
    case Locador = 'Locador';
    case NaoAplicavel = 'N/A';
}
