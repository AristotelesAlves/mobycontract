<?php

declare(strict_types=1);

namespace App\Modules\Leads\Contracts;

use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Modules\Leads\DTOs\Input\AlterarLeadData;
use App\Modules\Leads\DTOs\Input\AlterarSituacaoLeadData;
use App\Modules\Leads\DTOs\Input\ConverterLeadData;
use App\Modules\Leads\DTOs\Input\CriarLeadData;
use App\Modules\Leads\DTOs\Input\ListarLeadsFiltros;
use App\Modules\Leads\DTOs\Output\LeadResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;

interface LeadServiceInterface
{
    /**
     * @return PaginatedResult<LeadResult>
     */
    public function listar(ListarLeadsFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $leadId): LeadResult;

    public function armazenar(CriarLeadData $dados, int $autorId): LeadResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterar(AlterarLeadData $dados, int $autorId): LeadResult;

    /**
     * @throws RecursoNaoEncontradoException
     * @throws TransicaoDeStatusInvalidaException
     */
    public function alterarSituacao(AlterarSituacaoLeadData $dados, int $autorId): LeadResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function excluir(int $leadId, int $autorId): void;

    /**
     * @throws RecursoNaoEncontradoException
     * @throws TransicaoDeStatusInvalidaException
     */
    public function converter(int $leadId, ConverterLeadData $dados, int $autorId): InquilinoResult;
}
