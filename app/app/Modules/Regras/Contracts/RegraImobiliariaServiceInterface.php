<?php

declare(strict_types=1);

namespace App\Modules\Regras\Contracts;

use App\Modules\Regras\DTOs\Input\AtualizarRegrasData;
use App\Modules\Regras\DTOs\Output\RegraImobiliariaResult;

interface RegraImobiliariaServiceInterface
{
    public function obter(): RegraImobiliariaResult;

    public function atualizar(AtualizarRegrasData $dados): RegraImobiliariaResult;
}
