<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Input\AlterarAditivoData;
use App\Modules\Contratos\DTOs\Input\AlterarStatusAditivoData;
use App\Modules\Contratos\DTOs\Input\CriarAditivoData;
use App\Modules\Contratos\DTOs\Input\ListarAditivosFiltros;
use App\Modules\Contratos\DTOs\Output\AditivoResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;

interface AditivoServiceInterface
{
    /**
     * @return PaginatedResult<AditivoResult>
     */
    public function listar(ListarAditivosFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $aditivoId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function armazenar(CriarAditivoData $dados, int $autorId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterar(AlterarAditivoData $dados, int $autorId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     * @throws TransicaoDeStatusInvalidaException
     */
    public function alterarStatus(int $aditivoId, AlterarStatusAditivoData $dados, int $autorId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function assinar(int $aditivoId, int $assinaturaId, int $autorId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function confirmarDocumento(int $aditivoId, ?string $anexo, ?string $nomeAnexo, int $autorId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function registrarBlockchain(int $aditivoId, int $autorId): AditivoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function excluir(int $aditivoId, int $autorId): void;
}
