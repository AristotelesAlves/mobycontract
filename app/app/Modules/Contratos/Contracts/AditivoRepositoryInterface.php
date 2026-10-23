<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Input\ListarAditivosFiltros;
use App\Modules\Contratos\DTOs\Output\AditivoResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface AditivoRepositoryInterface
{
    /**
     * @return PaginatedResult<AditivoResult>
     */
    public function paginar(ListarAditivosFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $aditivoId): ?AditivoResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): AditivoResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $aditivoId, array $atributos): AditivoResult;

    public function excluir(int $aditivoId): void;

    public function proximoNumero(int $contratoId): string;

    /**
     * @param  list<array{papel: string, nome: string}>  $assinaturas
     */
    public function criarAssinaturas(int $aditivoId, array $assinaturas): void;

    public function marcarAssinatura(int $aditivoId, int $assinaturaId, string $momento): bool;

    public function todasAsAssinaturasAssinadas(int $aditivoId): bool;
}
