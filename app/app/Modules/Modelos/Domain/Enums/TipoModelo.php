<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Domain\Enums;

/**
 * Tipo de documento produzido por um modelo (R-8.3.1).
 */
enum TipoModelo: string
{
    case Contrato = 'Contrato';
    case Aditivo = 'Aditivo';
    case Distrato = 'Distrato';
    case Laudo = 'Laudo';

    public function exigeGarantia(): bool
    {
        return $this === self::Contrato || $this === self::Aditivo;
    }

    public function aceitaGarantia(): bool
    {
        return $this === self::Contrato || $this === self::Aditivo;
    }

    public function aceitaVariaveisDeAditivo(): bool
    {
        return $this === self::Aditivo;
    }
}
