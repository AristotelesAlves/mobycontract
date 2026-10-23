<?php

declare(strict_types=1);

namespace App\Modules\Leads\Domain\Enums;

enum OrigemLead: string
{
    case Site = 'Site';
    case Visita = 'Visita';
    case Indicacao = 'Indicação';
    case Telefone = 'Telefone';
    case Outro = 'Outro';
}
