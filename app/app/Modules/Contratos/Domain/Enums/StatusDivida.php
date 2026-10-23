<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

enum StatusDivida: string
{
    case EmAberto = 'Em aberto';
    case Quitada = 'Quitada';

    public function quitable(): bool
    {
        return $this === self::EmAberto;
    }
}
