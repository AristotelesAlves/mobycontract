<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Contracts;

use App\Modules\Imoveis\DTOs\Input\AlterarManutencaoData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoAgendamentoData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoInteressadoData;
use App\Modules\Imoveis\DTOs\Input\CriarAgendamentoData;
use App\Modules\Imoveis\DTOs\Input\CriarAnotacaoData;
use App\Modules\Imoveis\DTOs\Input\CriarInteressadoData;
use App\Modules\Imoveis\DTOs\Input\CriarManutencaoData;
use App\Modules\Imoveis\DTOs\Output\AgendamentoResult;
use App\Modules\Imoveis\DTOs\Output\AnotacaoResult;
use App\Modules\Imoveis\DTOs\Output\InteressadoResult;
use App\Modules\Imoveis\DTOs\Output\ManutencaoResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface ImovelSubRecursoServiceInterface
{
    /**
     * @return PaginatedResult<AnotacaoResult>
     */
    public function listarAnotacoes(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    public function criarAnotacao(CriarAnotacaoData $dados, int $autorId): AnotacaoResult;

    public function excluirAnotacao(int $imovelId, int $anotacaoId, int $autorId): void;

    /**
     * @return PaginatedResult<AgendamentoResult>
     */
    public function listarAgendamentos(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    public function criarAgendamento(CriarAgendamentoData $dados, int $autorId): AgendamentoResult;

    public function alterarSituacaoAgendamento(int $imovelId, int $agendamentoId, AlterarSituacaoAgendamentoData $dados, int $autorId): AgendamentoResult;

    /**
     * @return PaginatedResult<InteressadoResult>
     */
    public function listarInteressados(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    public function criarInteressado(CriarInteressadoData $dados, int $autorId): InteressadoResult;

    public function alterarSituacaoInteressado(int $imovelId, int $interessadoId, AlterarSituacaoInteressadoData $dados, int $autorId): InteressadoResult;

    /**
     * @return PaginatedResult<ManutencaoResult>
     */
    public function listarManutencoes(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult;

    public function criarManutencao(CriarManutencaoData $dados, int $autorId): ManutencaoResult;

    public function alterarManutencao(int $imovelId, int $manutencaoId, AlterarManutencaoData $dados, int $autorId): ManutencaoResult;
}
