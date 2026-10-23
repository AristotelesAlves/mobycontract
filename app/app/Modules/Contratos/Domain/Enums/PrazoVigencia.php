<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Prazo de vigência do contrato (R-4.5).
 */
enum PrazoVigencia: string
{
    case Determinado = 'Determinado';
    case Indeterminado = 'Indeterminado';
}
