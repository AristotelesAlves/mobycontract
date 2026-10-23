<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Domain\Enums;

enum TipoPessoa: string
{
    case Pf = 'PF';
    case Pj = 'PJ';
}
