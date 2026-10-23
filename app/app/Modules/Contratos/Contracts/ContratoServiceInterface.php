<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Input\AlterarContratoData;
use App\Modules\Contratos\DTOs\Input\AlterarStatusData;
use App\Modules\Contratos\DTOs\Input\CriarContratoData;
use App\Modules\Contratos\DTOs\Input\LancarEncargoData;
use App\Modules\Contratos\DTOs\Input\ListarContratosFiltros;
use App\Modules\Contratos\DTOs\Input\ListarParcelasFiltros;
use App\Modules\Contratos\DTOs\Input\RegistrarPagamentoData;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\DTOs\Output\EtapaResult;
use App\Modules\Contratos\DTOs\Output\ParcelaResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;

interface ContratoServiceInterface
{
    /**
     * @return PaginatedResult<ContratoResult>
     */
    public function listar(ListarContratosFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $contratoId): ContratoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function armazenar(CriarContratoData $dados, int $autorId): ContratoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterar(AlterarContratoData $dados, int $autorId): ContratoResult;

    /**
     * @throws RecursoNaoEncontradoException
     * @throws TransicaoDeStatusInvalidaException
     */
    public function alterarStatus(int $contratoId, AlterarStatusData $dados, int $autorId): ContratoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function excluir(int $contratoId, int $autorId): void;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function assinar(int $contratoId, int $assinaturaId, int $autorId): ContratoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function confirmarDocumentoFisico(int $contratoId, ?string $anexo, ?string $nomeAnexo, int $autorId): ContratoResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function registrarBlockchain(int $contratoId, int $autorId): ContratoResult;

    /**
     * @return PaginatedResult<ParcelaResult>
     *
     * @throws RecursoNaoEncontradoException
     */
    public function listarParcelas(ListarParcelasFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function registrarPagamento(int $contratoId, int $parcelaId, RegistrarPagamentoData $dados, int $autorId): ParcelaResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function lancarEncargo(int $contratoId, int $parcelaId, LancarEncargoData $dados, int $autorId): ParcelaResult;

    /**
     * @return array<string, int>
     */
    public function totais(): array;

    /**
     * @return list<EtapaResult>
     *
     * @throws RecursoNaoEncontradoException
     */
    public function etapas(int $contratoId): array;
}
