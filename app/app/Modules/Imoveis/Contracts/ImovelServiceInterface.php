<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Contracts;

use App\Modules\Imoveis\DTOs\Input\AlterarImovelData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoImovelData;
use App\Modules\Imoveis\DTOs\Input\CriarImovelData;
use App\Modules\Imoveis\DTOs\Input\ListarImoveisFiltros;
use App\Modules\Imoveis\DTOs\Output\CustoMensalResult;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Modules\Imoveis\DTOs\Output\OcupacaoResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;

interface ImovelServiceInterface
{
    /**
     * @return PaginatedResult<ImovelResult>
     */
    public function listar(ListarImoveisFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $imovelId): ImovelResult;

    public function armazenar(CriarImovelData $dados, int $autorId): ImovelResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterar(AlterarImovelData $dados, int $autorId): ImovelResult;

    /**
     * @throws RecursoNaoEncontradoException
     * @throws TransicaoDeStatusInvalidaException
     */
    public function alterarSituacao(AlterarSituacaoImovelData $dados, int $autorId): ImovelResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function excluir(int $imovelId, int $autorId): void;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function custoMensal(int $imovelId): CustoMensalResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function ocupacao(int $imovelId): OcupacaoResult;
}
