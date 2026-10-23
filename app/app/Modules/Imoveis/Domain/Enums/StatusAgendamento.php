<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain\Enums;

enum StatusAgendamento: string
{
    case Agendado = 'Agendado';
    case Realizado = 'Realizado';
    case Cancelado = 'Cancelado';
}
