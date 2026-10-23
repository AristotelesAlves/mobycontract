<?php

declare(strict_types=1);

namespace App\Modules\Leads\Contracts;

use App\Modules\Leads\DTOs\Input\ListarLeadsFiltros;
use App\Modules\Leads\DTOs\Output\LeadResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface LeadRepositoryInterface
{
    /**
     * @return PaginatedResult<LeadResult>
     */
    public function paginar(ListarLeadsFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $leadId): ?LeadResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): LeadResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $leadId, array $atributos): LeadResult;

    public function atualizarSituacao(int $leadId, string $situacao): LeadResult;

    public function excluir(int $leadId): void;
}
