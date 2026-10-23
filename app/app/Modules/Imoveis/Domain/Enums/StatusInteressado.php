<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain\Enums;

enum StatusInteressado: string
{
    case Novo = 'Novo';
    case EmContato = 'Em contato';
    case Desistiu = 'Desistiu';
    case Fechou = 'Fechou';
}
