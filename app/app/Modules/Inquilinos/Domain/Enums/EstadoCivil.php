<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Domain\Enums;

enum EstadoCivil: string
{
    case Solteiro = 'Solteiro(a)';
    case Casado = 'Casado(a)';
    case Divorciado = 'Divorciado(a)';
    case Viuvo = 'Viúvo(a)';
    case UniaoEstavel = 'União Estável';
}
