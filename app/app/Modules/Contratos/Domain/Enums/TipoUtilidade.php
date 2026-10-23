<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

enum TipoUtilidade: string
{
    case Agua = 'agua';
    case Luz = 'luz';
    case Gas = 'gas';
    case Internet = 'internet';
}
