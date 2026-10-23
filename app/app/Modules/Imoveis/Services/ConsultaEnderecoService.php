<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Services;

use App\Modules\Imoveis\Contracts\ConsultaEnderecoServiceInterface;
use App\Modules\Imoveis\Contracts\EnderecoCepRepositoryInterface;
use App\Modules\Imoveis\DTOs\Output\EnderecoCepResult;

final readonly class ConsultaEnderecoService implements ConsultaEnderecoServiceInterface
{
    public function __construct(
        private EnderecoCepRepositoryInterface $repositorio,
    ) {}

    public function consultarPorCep(string $cep): EnderecoCepResult
    {
        return $this->repositorio->consultar($cep);
    }
}
