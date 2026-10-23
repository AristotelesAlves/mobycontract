<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain\Enums;

enum TipoInteressado: string
{
    case Comprar = 'Comprar';
    case Alugar = 'Alugar';
    case Visitar = 'Visitar';
}
