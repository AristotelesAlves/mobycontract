<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Repositories\Eloquent;

use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\Domain\Enums\StatusParcela;
use App\Modules\Contratos\DTOs\Input\FiadorData;
use App\Modules\Contratos\DTOs\Input\ListarContratosFiltros;
use App\Modules\Contratos\DTOs\Input\ListarParcelasFiltros;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\DTOs\Output\ParcelaResult;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoAlteracao;
use App\Modules\Contratos\Models\ContratoAssinatura;
use App\Modules\Contratos\Models\ContratoEncargo;
use App\Modules\Contratos\Models\ContratoEncargoFixo;
use App\Modules\Contratos\Models\ContratoHistorico;
use App\Modules\Contratos\Models\ContratoParcela;
use App\Modules\Contratos\Models\ContratoParte;
use App\Modules\Contratos\Models\ContratoUtilidade;
use App\Modules\Contratos\Models\Divida;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\Relogio;
use App\Shared\Contracts\SequenciaNumericaInterface;
use Illuminate\Database\Eloquent\Builder;

final readonly class ContratoRepository implements ContratoRepositoryInterface
{
    public function __construct(
        private Contrato $modelo,
        private ContratoParcela $parcela,
        private ContratoAssinatura $assinatura,
        private ContratoParte $parte,
        private ContratoUtilidade $utilidade,
        private ContratoEncargoFixo $encargoFixo,
        private ContratoEncargo $encargo,
        private ContratoHistorico $historico,
        private ContratoAlteracao $alteracao,
        private SequenciaNumericaInterface $sequencias,
        private Relogio $relogio,
    ) {}

    private const array RELACOES_EXIBICAO = ['fiadores', 'assinaturas', 'imovel', 'inquilino', 'modelo', 'utilidades', 'encargosFixos'];

    /**
     * @return PaginatedResult<ContratoResult>
     */
    public function paginar(ListarContratosFiltros $filtros): PaginatedResult
    {
        $campos = array_values(array_diff($this->modelo->getFillable(), ['snapshot']));
        $consulta = $this->modelo->newQuery()
            ->select(['id', 'created_at', 'updated_at', ...$campos])
            ->selectRaw("CASE WHEN snapshot IS NULL THEN NULL ELSE jsonb_build_object('imovel', snapshot->'imovel', 'inquilino', jsonb_build_object('nome', snapshot->'inquilino'->>'nome'), 'modelo', jsonb_build_object('titulo', snapshot->'modelo'->>'titulo', 'versao', snapshot->'modelo'->'versao')) END AS snapshot")
            ->with(self::RELACOES_EXIBICAO)
            ->with(['modelo:id,titulo,versao', 'inquilino:id,nome']);

        if ($filtros->status instanceof StatusContrato) {
            $consulta->where('status', $filtros->status->value);
        }

        if ($filtros->imovelId !== null) {
            $consulta->where('imovel_id', $filtros->imovelId);
        }

        if ($filtros->inquilinoId !== null) {
            $consulta->where('inquilino_id', $filtros->inquilinoId);
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $termo = '%'.$filtros->texto.'%';
            $consulta->where(function (Builder $interna) use ($termo): void {
                $interna->whereRaw('unaccent(numero) ILIKE ?', [$termo])
                    ->orWhereRaw("unaccent(snapshot #>> '{inquilino,nome}') ILIKE ?", [$termo])
                    ->orWhereRaw("unaccent(snapshot #>> '{imovel,titulo}') ILIKE ?", [$termo])
                    ->orWhereHas('inquilino', fn (Builder $q) => $q->whereRaw('unaccent(nome) ILIKE ?', [$termo]))
                    ->orWhereHas('imovel', fn (Builder $q) => $q->whereRaw('unaccent(titulo) ILIKE ?', [$termo]));
            });
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<ContratoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            fn (Contrato $contrato): ContratoResult => ContratoResult::fromModel($contrato, $this->hoje()),
        );

        return $resultado;
    }

    public function buscarPorId(int $contratoId): ?ContratoResult
    {
        $contrato = $this->modelo->newQuery()
            ->with([...self::RELACOES_EXIBICAO, 'historicos'])
            ->find($contratoId);

        return $contrato === null ? null : ContratoResult::fromModel($contrato, $this->hoje());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): ContratoResult
    {
        $criado = $this->modelo->newQuery()->create($atributos);

        return ContratoResult::fromModel(
            $criado->refresh()->loadMissing(self::RELACOES_EXIBICAO),
            $this->hoje(),
        );
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $contratoId, array $atributos): ContratoResult
    {
        $contrato = $this->modelo->newQuery()->lockForUpdate()->findOrFail($contratoId);
        $descontoAlterado = isset($atributos['desconto_mensal'])
            && is_numeric($atributos['desconto_mensal'])
            && (float) $atributos['desconto_mensal'] !== (float) $contrato->desconto_mensal;
        $primeira = $descontoAlterado
            ? $this->parcela->newQuery()->where('contrato_id', $contratoId)->orderBy('vencimento')->orderBy('id')->lockForUpdate()->first()
            : null;
        $novoValor = $primeira !== null
            ? (float) $primeira->valor + (float) $contrato->desconto_mensal - (float) $atributos['desconto_mensal']
            : null;

        if ($primeira !== null && $novoValor !== null && ($primeira->status === 'Pago' || $novoValor < 0)) {
            throw new RegraNegocioException('Não é possível alterar o desconto de uma parcela paga ou ultrapassar seu principal.');
        }

        if ($primeira !== null && $novoValor !== null) {
            $primeira->update(['valor' => round($novoValor, 2)]);
        }

        $caucaoAlterada = array_key_exists('valor_caucao', $atributos)
            && is_numeric($atributos['valor_caucao'])
            && (float) $atributos['valor_caucao'] !== (float) $contrato->valor_caucao;
        $divida = $caucaoAlterada
            ? Divida::query()->where('contrato_id', $contratoId)->where('descricao', 'Caução')->lockForUpdate()->first()
            : null;

        if ($divida !== null && $divida->status !== 'Em aberto') {
            throw new RegraNegocioException('Não é possível alterar a caução já quitada.');
        }

        if ($divida !== null) {
            $divida->update(['valor' => $atributos['valor_caucao']]);
        }
        $contrato->fill($atributos)->save();

        return ContratoResult::fromModel(
            $contrato->refresh()->loadMissing(self::RELACOES_EXIBICAO),
            $this->hoje(),
        );
    }

    public function excluir(int $contratoId): bool
    {
        return $this->modelo->newQuery()
            ->whereKey($contratoId)
            ->where('status', StatusContrato::Rascunho->value)
            ->delete() === 1;
    }

    public function proximoNumero(string $ano): string
    {
        $sequencia = $this->sequencias->proximo('contrato');

        return sprintf('%d/%04d', (int) $ano, ((int) $ano % 10) * 1000 + $sequencia);
    }

    public function existeContratoAtivoParaImovel(int $imovelId, ?int $ignorarContratoId = null): bool
    {
        return $this->consultaAtivo()
            ->where('imovel_id', $imovelId)
            ->when($ignorarContratoId !== null, fn (Builder $q) => $q->whereKeyNot($ignorarContratoId))
            ->exists();
    }

    public function existeContratoAtivoParaInquilino(int $inquilinoId, ?int $ignorarContratoId = null): bool
    {
        return $this->consultaAtivo()
            ->where('inquilino_id', $inquilinoId)
            ->when($ignorarContratoId !== null, fn (Builder $q) => $q->whereKeyNot($ignorarContratoId))
            ->exists();
    }

    /**
     * @return array<string, int>
     */
    public function totaisPorStatus(): array
    {
        /** @var array<string, int> $totais */
        $totais = $this->modelo->newQuery()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return $totais;
    }

    /**
     * @param  list<FiadorData>  $fiadores
     */
    public function substituirFiadores(int $contratoId, array $fiadores): void
    {
        $this->parte->newQuery()
            ->where('contrato_id', $contratoId)
            ->where('tipo', 'fiador')
            ->delete();

        foreach ($fiadores as $fiador) {
            $this->parte->newQuery()->create([
                'contrato_id' => $contratoId,
                'tipo' => 'fiador',
                'nome' => $fiador->nome,
                'cpf' => $fiador->cpf,
                'telefone' => $fiador->telefone,
            ]);
        }
    }

    /**
     * @param  list<array{tipo: string, responsabilidade: string}>  $utilidades
     */
    public function registrarUtilidades(int $contratoId, array $utilidades): void
    {
        foreach ($utilidades as $utilidade) {
            $this->utilidade->newQuery()->firstOrCreate(
                [
                    'contrato_id' => $contratoId,
                    'tipo' => $utilidade['tipo'],
                ],
                [
                    'responsabilidade' => $utilidade['responsabilidade'],
                    'valor_estimado' => 0,
                    'ativo' => true,
                ],
            );
        }
    }

    /**
     * @param  list<array{tipo: string, responsabilidade: string, valor_estimado: ?float}>  $encargos
     */
    public function registrarEncargosFixos(int $contratoId, array $encargos): void
    {
        foreach ($encargos as $encargo) {
            $this->encargoFixo->newQuery()->firstOrCreate(
                [
                    'contrato_id' => $contratoId,
                    'tipo' => $encargo['tipo'],
                ],
                [
                    'responsabilidade' => $encargo['responsabilidade'],
                    'valor_estimado' => $encargo['valor_estimado'] ?? 0,
                    'ativo' => true,
                ],
            );
        }
    }

    /**
     * @param  list<array{papel: string, nome: string}>  $assinaturas
     */
    public function criarAssinaturas(int $contratoId, array $assinaturas): void
    {
        foreach ($assinaturas as $assinatura) {
            $this->assinatura->newQuery()->create([
                'contrato_id' => $contratoId,
                'papel' => $assinatura['papel'],
                'nome' => $assinatura['nome'],
            ]);
        }
    }

    public function marcarAssinatura(int $contratoId, int $assinaturaId, string $momento): bool
    {
        return $this->assinatura->newQuery()
            ->where('contrato_id', $contratoId)
            ->whereKey($assinaturaId)
            ->update([
                'assinado' => true,
                'data_assinatura' => $momento,
            ]) === 1;
    }

    public function todasAsAssinaturasAssinadas(int $contratoId): bool
    {
        return $this->assinatura->newQuery()
            ->where('contrato_id', $contratoId)
            ->where('assinado', false)
            ->doesntExist();
    }

    public function possuiAssinatura(int $contratoId): bool
    {
        return $this->assinatura->newQuery()
            ->where('contrato_id', $contratoId)
            ->exists();
    }

    public function estornarParcelasNaoPagas(int $contratoId): void
    {
        $this->parcela->newQuery()
            ->where('contrato_id', $contratoId)
            ->where('status', '!=', StatusParcela::Pago->value)
            ->update(['status' => StatusParcela::Pendente->value]);
    }

    /**
     * @param  list<array<string, mixed>>  $parcelas
     */
    public function criarParcelas(int $contratoId, array $parcelas): void
    {
        foreach ($parcelas as $parcela) {
            $this->parcela->newQuery()->create([
                'contrato_id' => $contratoId,
                ...$parcela,
            ]);
        }
    }

    public function existeParcelaComReferencia(int $contratoId, string $referencia, ?int $ignorarParcelaId = null): bool
    {
        return $this->parcela->newQuery()
            ->where('contrato_id', $contratoId)
            ->where('referencia', $referencia)
            ->when($ignorarParcelaId !== null, fn (Builder $q) => $q->whereKeyNot($ignorarParcelaId))
            ->exists();
    }

    public function contarParcelas(int $contratoId): int
    {
        return $this->parcela->newQuery()->where('contrato_id', $contratoId)->count();
    }

    /**
     * @return PaginatedResult<ParcelaResult>
     */
    public function paginarParcelas(ListarParcelasFiltros $filtros): PaginatedResult
    {
        $consulta = $this->parcela->newQuery()->with('encargos')->where('contrato_id', $filtros->contratoId);

        if ($filtros->status instanceof StatusParcela) {
            $consulta->where('status', $filtros->status->value);
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $termo = '%'.$filtros->texto.'%';
            $consulta->whereRaw('unaccent(referencia) ILIKE ?', [$termo]);
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<ParcelaResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (ContratoParcela $parcela): ParcelaResult => ParcelaResult::fromModel($parcela),
        );

        return $resultado;
    }

    public function buscarParcela(int $contratoId, int $parcelaId): ?ParcelaResult
    {
        $parcela = $this->parcela->newQuery()
            ->where('contrato_id', $contratoId)
            ->lockForUpdate()
            ->find($parcelaId);

        return $parcela === null ? null : ParcelaResult::fromModel($parcela);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizarParcela(int $parcelaId, array $atributos): ParcelaResult
    {
        $parcela = $this->parcela->newQuery()->findOrFail($parcelaId);
        $parcela->fill($atributos)->save();

        return ParcelaResult::fromModel($parcela->refresh());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function registrarEncargo(int $contratoId, int $parcelaId, array $atributos): void
    {
        $this->encargo->newQuery()->create([
            'contrato_id' => $contratoId,
            'parcela_id' => $parcelaId,
            ...$atributos,
        ]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function registrarHistorico(int $contratoId, array $atributos): void
    {
        $this->historico->newQuery()->create([
            'contrato_id' => $contratoId,
            ...$atributos,
        ]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function registrarAlteracao(int $contratoId, array $atributos): void
    {
        $this->alteracao->newQuery()->create([
            'contrato_id' => $contratoId,
            ...$atributos,
        ]);
    }

    /**
     * @return Builder<Contrato>
     */
    private function consultaAtivo(): Builder
    {
        /**
         * Todo contrato não-terminal reserva o imóvel/inquilino: evita duas
         * minutas concorrentes para o mesmo destino.
         */
        return $this->modelo->newQuery()->whereNotIn('status', ['Encerrado', 'Cancelado']);
    }

    private function hoje(): string
    {
        return $this->relogio->hoje();
    }
}
