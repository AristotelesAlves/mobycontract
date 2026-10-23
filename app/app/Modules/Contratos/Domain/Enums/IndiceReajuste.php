<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Índice de reajuste do aluguel (R-4.5.28).
 */
enum IndiceReajuste: string
{
    case IPCA = 'IPCA';
    case IGPM = 'IGP-M';
    case INPC = 'INPC';
    case IPET = 'IPET';
}
