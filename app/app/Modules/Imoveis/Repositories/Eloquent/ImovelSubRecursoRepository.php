<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Repositories\Eloquent;

use App\Modules\Imoveis\Contracts\ImovelSubRecursoRepositoryInterface;
use App\Modules\Imoveis\Domain\Enums\StatusInteressado;
use App\Modules\Imoveis\Domain\Enums\TipoInteressado;
use App\Modules\Imoveis\DTOs\Output\AgendamentoResult;
use App\Modules\Imoveis\DTOs\Output\AnotacaoResult;
use App\Modules\Imoveis\DTOs\Output\InteressadoResult;
use App\Modules\Imoveis\DTOs\Output\ManutencaoResult;
use App\Modules\Imoveis\Models\Agendamento;
use App\Modules\Imoveis\Models\Anotacao;
use App\Modules\Imoveis\Models\Interessado;
use App\Modules\Imoveis\Models\Manutencao;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ImovelSubRecursoRepository implements ImovelSubRecursoRepositoryInterface
{
    public function __construct(
        private Anotacao $anotacao,
        private Agendamento $agendamento,
        private Interessado $interessado,
        private Manutencao $manutencao,
    ) {}

    /**
     * @return PaginatedResult<AnotacaoResult>
     */
    public function listarAnotacoes(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $consulta = $this->anotacao->newQuery()
            ->with('autor')
            ->where('imovel_id', $imovelId)
            ->orderByDesc('id');

        $paginador = $consulta->paginate($porPagina, ['*'], 'pagina', $pagina);

        /** @var PaginatedResult<AnotacaoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $paginador,
            static fn (Anotacao $item): AnotacaoResult => AnotacaoResult::fromModel($item),
        );

        return $resultado;
    }

    public function criarAnotacao(int $imovelId, string $texto, int $autorId): AnotacaoResult
    {
        $instancia = $this->anotacao->newQuery()->create([
            'imovel_id' => $imovelId,
            'texto' => $texto,
            'autor_id' => $autorId,
        ]);

        $instancia->load('autor');

        return AnotacaoResult::fromModel($instancia);
    }

    public function excluirAnotacao(int $imovelId, int $anotacaoId): void
    {
        $this->anotacao->newQuery()
            ->where('imovel_id', $imovelId)
            ->where('id', $anotacaoId)
            ->delete();
    }

    /**
     * @return PaginatedResult<AgendamentoResult>
     */
    public function listarAgendamentos(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $consulta = $this->agendamento->newQuery()
            ->with('interessado')
            ->where('imovel_id', $imovelId)
            ->orderByDesc('data')
            ->orderByDesc('hora');

        if ($status !== null && $status !== '') {
            $consulta->where('status', $status);
        }

        $paginador = $consulta->paginate($porPagina, ['*'], 'pagina', $pagina);

        /** @var PaginatedResult<AgendamentoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $paginador,
            static fn (Agendamento $item): AgendamentoResult => AgendamentoResult::fromModel($item),
        );

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarAgendamento(int $imovelId, array $dados): AgendamentoResult
    {
        $interessado = $this->resolverOuCriarInteressado($imovelId, $dados);

        $instancia = $this->agendamento->newQuery()->create([
            'imovel_id' => $imovelId,
            'interessado_id' => $interessado->id,
            'data' => $dados['data'],
            'hora' => $dados['hora'],
            'telefone_contato' => $dados['telefone'] ?? null,
            'status' => 'Agendado',
        ]);

        $instancia->load('interessado');

        return AgendamentoResult::fromModel($instancia);
    }

    /**
     * Associa o nome informado a um interessado do imóvel, criando o registro
     * quando ainda não existir (agendamentos referenciam `interessado_id`).
     *
     * @param  array<string, mixed>  $dados
     */
    private function resolverOuCriarInteressado(int $imovelId, array $dados): Interessado
    {
        $nome = DadosDeEntrada::texto($dados, 'interessado');

        $existente = $this->interessado->newQuery()
            ->where('imovel_id', $imovelId)
            ->where('nome', $nome)
            ->first();

        if ($existente instanceof Interessado) {
            return $existente;
        }

        /** @var Interessado $criado */
        $criado = $this->interessado->newQuery()->create([
            'imovel_id' => $imovelId,
            'nome' => $nome,
            'telefone' => $dados['telefone'] ?? null,
            'tipo' => TipoInteressado::Visitar->value,
            'status' => StatusInteressado::Novo->value,
        ]);

        return $criado;
    }

    public function atualizarSituacaoAgendamento(int $imovelId, int $agendamentoId, string $status): AgendamentoResult
    {
        $instancia = $this->agendamento->newQuery()
            ->with('interessado')
            ->where('imovel_id', $imovelId)
            ->where('id', $agendamentoId)
            ->firstOrFail();

        $instancia->update(['status' => $status]);

        return AgendamentoResult::fromModel($instancia);
    }

    /**
     * @return PaginatedResult<InteressadoResult>
     */
    public function listarInteressados(int $imovelId, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $consulta = $this->interessado->newQuery()
            ->where('imovel_id', $imovelId)
            ->orderByDesc('id');

        $paginador = $consulta->paginate($porPagina, ['*'], 'pagina', $pagina);

        /** @var PaginatedResult<InteressadoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $paginador,
            static fn (Interessado $item): InteressadoResult => InteressadoResult::fromModel($item),
        );

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarInteressado(int $imovelId, array $dados): InteressadoResult
    {
        $instancia = $this->interessado->newQuery()->create([
            'imovel_id' => $imovelId,
            'nome' => $dados['nome'],
            'telefone' => $dados['telefone'],
            'email' => $dados['email'] ?? null,
            'tipo' => $dados['tipo'],
            'status' => 'Novo',
        ]);

        return InteressadoResult::fromModel($instancia);
    }

    public function atualizarSituacaoInteressado(int $imovelId, int $interessadoId, string $status): InteressadoResult
    {
        $instancia = $this->interessado->newQuery()
            ->where('imovel_id', $imovelId)
            ->where('id', $interessadoId)
            ->firstOrFail();

        $instancia->update(['status' => $status]);

        return InteressadoResult::fromModel($instancia);
    }

    /**
     * @return PaginatedResult<ManutencaoResult>
     */
    public function listarManutencoes(int $imovelId, ?string $status = null, int $pagina = 1, int $porPagina = 15): PaginatedResult
    {
        $consulta = $this->manutencao->newQuery()
            ->where('imovel_id', $imovelId)
            ->orderByDesc('id');

        if ($status !== null && $status !== '') {
            $consulta->where('status', $status);
        }

        $paginador = $consulta->paginate($porPagina, ['*'], 'pagina', $pagina);

        /** @var PaginatedResult<ManutencaoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $paginador,
            static fn (Manutencao $item): ManutencaoResult => ManutencaoResult::fromModel($item),
        );

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarManutencao(int $imovelId, array $dados): ManutencaoResult
    {
        $instancia = $this->manutencao->newQuery()->create([
            'imovel_id' => $imovelId,
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'] ?? null,
            'responsavel' => $dados['responsavel'] ?? null,
            'custo' => $dados['custo'] ?? 0,
            'status' => 'Aberto',
        ]);

        return ManutencaoResult::fromModel($instancia);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizarManutencao(int $imovelId, int $manutencaoId, array $dados): ManutencaoResult
    {
        $instancia = $this->manutencao->newQuery()
            ->where('imovel_id', $imovelId)
            ->where('id', $manutencaoId)
            ->firstOrFail();

        $instancia->update($dados);

        return ManutencaoResult::fromModel($instancia);
    }
}
