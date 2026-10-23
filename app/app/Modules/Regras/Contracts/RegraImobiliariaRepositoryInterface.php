<?php

declare(strict_types=1);

namespace App\Modules\Regras\Contracts;

use App\Modules\Regras\DTOs\Output\RegraImobiliariaResult;

interface RegraImobiliariaRepositoryInterface
{
    public function obter(): RegraImobiliariaResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $id, array $atributos): RegraImobiliariaResult;
}
