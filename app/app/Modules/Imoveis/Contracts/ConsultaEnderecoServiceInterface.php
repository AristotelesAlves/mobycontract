<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Contracts;

use App\Modules\Imoveis\DTOs\Output\EnderecoCepResult;

interface ConsultaEnderecoServiceInterface
{
    public function consultarPorCep(string $cep): EnderecoCepResult;
}
