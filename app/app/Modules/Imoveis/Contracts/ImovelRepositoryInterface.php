<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Contracts;

use App\Modules\Imoveis\DTOs\Input\ListarImoveisFiltros;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface ImovelRepositoryInterface
{
    /**
     * @return PaginatedResult<ImovelResult>
     */
    public function paginar(ListarImoveisFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $imovelId): ?ImovelResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): ImovelResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $imovelId, array $atributos): ImovelResult;

    public function atualizarSituacao(int $imovelId, string $situacao): ImovelResult;

    public function excluir(int $imovelId): void;
}
