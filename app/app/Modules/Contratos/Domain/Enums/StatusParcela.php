<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Status da parcela financeira (R-5.4).
 */
enum StatusParcela: string
{
    case Pendente = 'Pendente';
    case Atrasado = 'Atrasado';
    case Pago = 'Pago';

    public function estaPago(): bool
    {
        return $this === self::Pago;
    }
}
