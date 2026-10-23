<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain\Enums;

enum StatusManutencao: string
{
    case Aberto = 'Aberto';
    case EmAndamento = 'Em andamento';
    case Concluido = 'Concluido';
    case Cancelado = 'Cancelado';
}
