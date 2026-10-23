<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Domain\Enums;

enum EstadoVistoriaItem: string
{
    case Otimo = 'Ótimo';
    case Bom = 'Bom';
    case Regular = 'Regular';
    case Ruim = 'Ruim';
    case Substituir = 'Substituir';
}
