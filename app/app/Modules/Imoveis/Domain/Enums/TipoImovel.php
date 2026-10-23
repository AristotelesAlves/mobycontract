<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain\Enums;

enum TipoImovel: string
{
    case Apartamento = 'Apartamento';
    case Casa = 'Casa';
    case Studio = 'Studio';
    case Cobertura = 'Cobertura';
    case Comercial = 'Comercial';
    case Terreno = 'Terreno';
}
