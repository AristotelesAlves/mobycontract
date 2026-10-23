<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Destino do consumo de um serviço no contrato (R-12.8.5.5).
 */
enum Consumo: string
{
    case Inquilino = 'Inquilino';
    case Locador = 'Locador';
    case InclusaNoCondominio = 'Inclusa no Condomínio';
}
