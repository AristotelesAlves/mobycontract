<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Contracts;

use App\Modules\Imoveis\DTOs\Output\AgendamentoResult;
use App\Modules\Imoveis\DTOs\Output\AnotacaoResult;
use App\Modules\Imoveis\DTOs\Output\InteressadoResult;
use App\Modules\Imoveis\DTOs\Output\ManutencaoResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface ImovelSubRecursoRepositoryInterface
{
    /**
     * @return PaginatedResult<AnotacaoResult>
     */
    public function listarAnotacoes(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    public function criarAnotacao(int $imovelId, string $texto, int $autorId): AnotacaoResult;

    public function excluirAnotacao(int $imovelId, int $anotacaoId): void;

    /**
     * @return PaginatedResult<AgendamentoResult>
     */
    public function listarAgendamentos(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarAgendamento(int $imovelId, array $dados): AgendamentoResult;

    public function atualizarSituacaoAgendamento(int $imovelId, int $agendamentoId, string $status): AgendamentoResult;

    /**
     * @return PaginatedResult<InteressadoResult>
     */
    public function listarInteressados(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarInteressado(int $imovelId, array $dados): InteressadoResult;

    public function atualizarSituacaoInteressado(int $imovelId, int $interessadoId, string $status): InteressadoResult;

    /**
     * @return PaginatedResult<ManutencaoResult>
     */
    public function listarManutencoes(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarManutencao(int $imovelId, array $dados): ManutencaoResult;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizarManutencao(int $imovelId, int $manutencaoId, array $dados): ManutencaoResult;
}
