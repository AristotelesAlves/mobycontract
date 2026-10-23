<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Domain\Enums;

enum TipoVistoria: string
{
    case Entrada = 'Entrada';
    case Retirada = 'Retirada';
    case Periodica = 'Periódica';
}
