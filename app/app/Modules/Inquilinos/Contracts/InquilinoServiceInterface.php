<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Contracts;

use App\Modules\Inquilinos\DTOs\Input\AlterarInquilinoData;
use App\Modules\Inquilinos\DTOs\Input\CriarInquilinoData;
use App\Modules\Inquilinos\DTOs\Input\ListarInquilinosFiltros;
use App\Modules\Inquilinos\DTOs\Output\IndicadoresInquilinoResult;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;

interface InquilinoServiceInterface
{
    /**
     * @return PaginatedResult<InquilinoResult>
     */
    public function listar(ListarInquilinosFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $inquilinoId): InquilinoResult;

    public function armazenar(CriarInquilinoData $dados, int $autorId): InquilinoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterar(AlterarInquilinoData $dados, int $autorId): InquilinoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function excluir(int $inquilinoId, int $autorId): void;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function indicadores(int $inquilinoId): IndicadoresInquilinoResult;
}
