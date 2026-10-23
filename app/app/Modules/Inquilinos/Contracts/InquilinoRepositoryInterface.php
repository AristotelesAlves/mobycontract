<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Contracts;

use App\Modules\Inquilinos\DTOs\Input\ListarInquilinosFiltros;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface InquilinoRepositoryInterface
{
    /**
     * @return PaginatedResult<InquilinoResult>
     */
    public function paginar(ListarInquilinosFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $inquilinoId): ?InquilinoResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): InquilinoResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $inquilinoId, array $atributos): InquilinoResult;

    public function excluir(int $inquilinoId): void;
}
