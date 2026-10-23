<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Contracts;

use App\Modules\Modelos\DTOs\Input\ListarModelosFiltros;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface ModeloRepositoryInterface
{
    /**
     * @return PaginatedResult<ModeloResult>
     */
    public function paginar(ListarModelosFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $modeloId): ?ModeloResult;

    public function proximoCodigo(): int;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): ModeloResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $modeloId, array $atributos): ModeloResult;

    public function definirAtivo(int $modeloId, bool $ativo): ModeloResult;
}
