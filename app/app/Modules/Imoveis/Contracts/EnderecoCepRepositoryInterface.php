<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Contracts;

use App\Modules\Imoveis\DTOs\Output\EnderecoCepResult;

interface EnderecoCepRepositoryInterface
{
    public function consultar(string $cep): EnderecoCepResult;
}
