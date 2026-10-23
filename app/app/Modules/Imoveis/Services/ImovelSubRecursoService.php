<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Services;

use App\Modules\Imoveis\Contracts\ImovelRepositoryInterface;
use App\Modules\Imoveis\Contracts\ImovelSubRecursoRepositoryInterface;
use App\Modules\Imoveis\Contracts\ImovelSubRecursoServiceInterface;
use App\Modules\Imoveis\DTOs\Input\AlterarManutencaoData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoAgendamentoData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoInteressadoData;
use App\Modules\Imoveis\DTOs\Input\CriarAgendamentoData;
use App\Modules\Imoveis\DTOs\Input\CriarAnotacaoData;
use App\Modules\Imoveis\DTOs\Input\CriarInteressadoData;
use App\Modules\Imoveis\DTOs\Input\CriarManutencaoData;
use App\Modules\Imoveis\DTOs\Output\AgendamentoResult;
use App\Modules\Imoveis\DTOs\Output\AnotacaoResult;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Modules\Imoveis\DTOs\Output\InteressadoResult;
use App\Modules\Imoveis\DTOs\Output\ManutencaoResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;

final readonly class ImovelSubRecursoService implements ImovelSubRecursoServiceInterface
{
    public function __construct(
        private ImovelRepositoryInterface $imoveis,
        private ImovelSubRecursoRepositoryInterface $subrecursos,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
    ) {}

    /**
     * @return PaginatedResult<AnotacaoResult>
     */
    public function listarAnotacoes(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $this->garantirImovelExistente($imovelId);

        return $this->subrecursos->listarAnotacoes($imovelId, max(1, $pagina), max(1, min(100, $porPagina)));
    }

    public function criarAnotacao(CriarAnotacaoData $dados, int $autorId): AnotacaoResult
    {
        $this->garantirImovelExistente($dados->imovelId);

        return $this->transacao->executar(function () use ($dados, $autorId): AnotacaoResult {
            $resultado = $this->subrecursos->criarAnotacao($dados->imovelId, $dados->texto, $autorId);

            $this->eventos->publicar('imovel.anotacao_criada', [
                'imovelId' => $dados->imovelId,
                'anotacaoId' => $resultado->id,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    public function excluirAnotacao(int $imovelId, int $anotacaoId, int $autorId): void
    {
        $this->garantirImovelExistente($imovelId);

        $this->transacao->executar(function () use ($imovelId, $anotacaoId, $autorId): void {
            $this->subrecursos->excluirAnotacao($imovelId, $anotacaoId);

            $this->eventos->publicar('imovel.anotacao_excluida', [
                'imovelId' => $imovelId,
                'anotacaoId' => $anotacaoId,
                'autorId' => $autorId,
            ]);
        });
    }

    /**
     * @return PaginatedResult<AgendamentoResult>
     */
    public function listarAgendamentos(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $this->garantirImovelExistente($imovelId);

        return $this->subrecursos->listarAgendamentos($imovelId, $status, max(1, $pagina), max(1, min(100, $porPagina)));
    }

    public function criarAgendamento(CriarAgendamentoData $dados, int $autorId): AgendamentoResult
    {
        $this->garantirImovelExistente($dados->imovelId);

        return $this->transacao->executar(function () use ($dados, $autorId): AgendamentoResult {
            $resultado = $this->subrecursos->criarAgendamento($dados->imovelId, [
                'data' => $dados->data,
                'hora' => $dados->hora,
                'interessado' => $dados->interessado,
                'telefone' => $dados->telefone,
            ]);

            $this->eventos->publicar('imovel.agendamento_criado', [
                'imovelId' => $dados->imovelId,
                'agendamentoId' => $resultado->id,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    public function alterarSituacaoAgendamento(int $imovelId, int $agendamentoId, AlterarSituacaoAgendamentoData $dados, int $autorId): AgendamentoResult
    {
        $this->garantirImovelExistente($imovelId);

        return $this->transacao->executar(function () use ($imovelId, $agendamentoId, $dados, $autorId): AgendamentoResult {
            $resultado = $this->subrecursos->atualizarSituacaoAgendamento($imovelId, $agendamentoId, $dados->status);

            $this->eventos->publicar('imovel.agendamento_situacao_alterada', [
                'imovelId' => $imovelId,
                'agendamentoId' => $agendamentoId,
                'status' => $dados->status,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    /**
     * @return PaginatedResult<InteressadoResult>
     */
    public function listarInteressados(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $this->garantirImovelExistente($imovelId);

        return $this->subrecursos->listarInteressados($imovelId, max(1, $pagina), max(1, min(100, $porPagina)));
    }

    public function criarInteressado(CriarInteressadoData $dados, int $autorId): InteressadoResult
    {
        $this->garantirImovelExistente($dados->imovelId);

        return $this->transacao->executar(function () use ($dados, $autorId): InteressadoResult {
            $resultado = $this->subrecursos->criarInteressado($dados->imovelId, [
                'nome' => $dados->nome,
                'telefone' => $dados->telefone,
                'email' => $dados->email,
                'tipo' => $dados->tipo,
            ]);

            $this->eventos->publicar('imovel.interessado_criado', [
                'imovelId' => $dados->imovelId,
                'interessadoId' => $resultado->id,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    public function alterarSituacaoInteressado(int $imovelId, int $interessadoId, AlterarSituacaoInteressadoData $dados, int $autorId): InteressadoResult
    {
        $this->garantirImovelExistente($imovelId);

        return $this->transacao->executar(function () use ($imovelId, $interessadoId, $dados, $autorId): InteressadoResult {
            $resultado = $this->subrecursos->atualizarSituacaoInteressado($imovelId, $interessadoId, $dados->status);

            $this->eventos->publicar('imovel.interessado_situacao_alterada', [
                'imovelId' => $imovelId,
                'interessadoId' => $interessadoId,
                'status' => $dados->status,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    /**
     * @return PaginatedResult<ManutencaoResult>
     */
    public function listarManutencoes(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $this->garantirImovelExistente($imovelId);

        return $this->subrecursos->listarManutencoes($imovelId, $status, max(1, $pagina), max(1, min(100, $porPagina)));
    }

    public function criarManutencao(CriarManutencaoData $dados, int $autorId): ManutencaoResult
    {
        $this->garantirImovelExistente($dados->imovelId);

        return $this->transacao->executar(function () use ($dados, $autorId): ManutencaoResult {
            $resultado = $this->subrecursos->criarManutencao($dados->imovelId, [
                'titulo' => $dados->titulo,
                'descricao' => $dados->descricao,
                'responsavel' => $dados->responsavel,
                'custo' => $dados->custo ?? 0,
            ]);

            $this->eventos->publicar('imovel.manutencao_criada', [
                'imovelId' => $dados->imovelId,
                'manutencaoId' => $resultado->id,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    public function alterarManutencao(int $imovelId, int $manutencaoId, AlterarManutencaoData $dados, int $autorId): ManutencaoResult
    {
        $this->garantirImovelExistente($imovelId);

        $atributos = array_filter([
            'titulo' => $dados->titulo,
            'descricao' => $dados->descricao,
            'responsavel' => $dados->responsavel,
            'custo' => $dados->custo,
            'status' => $dados->status,
        ], static fn (mixed $v): bool => $v !== null);

        return $this->transacao->executar(function () use ($imovelId, $manutencaoId, $atributos, $autorId): ManutencaoResult {
            $resultado = $this->subrecursos->atualizarManutencao($imovelId, $manutencaoId, $atributos);

            $this->eventos->publicar('imovel.manutencao_alterada', [
                'imovelId' => $imovelId,
                'manutencaoId' => $manutencaoId,
                'autorId' => $autorId,
            ]);

            return $resultado;
        });
    }

    private function garantirImovelExistente(int $imovelId): void
    {
        if (! $this->imoveis->buscarPorId($imovelId) instanceof ImovelResult) {
            throw RecursoNaoEncontradoException::imovel($imovelId);
        }
    }
}
