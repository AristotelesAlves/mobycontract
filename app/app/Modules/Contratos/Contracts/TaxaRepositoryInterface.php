<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Output\TaxaResult;

interface TaxaRepositoryInterface
{
    /**
     * @return list<TaxaResult>
     */
    public function listarPorContrato(int $contratoId): array;

    public function buscarPorId(int $taxaId): ?TaxaResult;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criar(int $contratoId, array $dados): TaxaResult;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(int $taxaId, array $dados): TaxaResult;

    public function excluir(int $taxaId): void;
}
