<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Domain\Enums;

/**
 * Filtro de vínculo do locatário com contratos (R-12.7.1).
 */
enum FiltroDeContrato: string
{
    case ComContrato = 'com_contrato';
    case SemContrato = 'sem_contrato';
}
