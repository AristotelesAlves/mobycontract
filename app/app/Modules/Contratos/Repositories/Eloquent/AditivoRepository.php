<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Repositories\Eloquent;

use App\Modules\Contratos\Contracts\AditivoRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Modules\Contratos\DTOs\Input\ListarAditivosFiltros;
use App\Modules\Contratos\DTOs\Output\AditivoResult;
use App\Modules\Contratos\Models\ContratoAditivo;
use App\Modules\Contratos\Models\ContratoAditivoAssinatura;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Contracts\Relogio;

final readonly class AditivoRepository implements AditivoRepositoryInterface
{
    private const array RELACOES_EXIBICAO = ['assinaturas', 'modelo'];

    public function __construct(
        private ContratoAditivo $modelo,
        private ContratoAditivoAssinatura $assinatura,
        private Relogio $relogio,
    ) {}

    /**
     * @return PaginatedResult<AditivoResult>
     */
    public function paginar(ListarAditivosFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery()
            ->with(self::RELACOES_EXIBICAO)
            ->where('contrato_id', $filtros->contratoId);

        if ($filtros->status instanceof StatusAditivo) {
            $consulta->where('status', $filtros->status->value);
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<AditivoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (ContratoAditivo $aditivo): AditivoResult => AditivoResult::fromModel($aditivo),
        );

        return $resultado;
    }

    public function buscarPorId(int $aditivoId): ?AditivoResult
    {
        $aditivo = $this->modelo->newQuery()->with(self::RELACOES_EXIBICAO)->find($aditivoId);

        return $aditivo === null ? null : AditivoResult::fromModel($aditivo);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): AditivoResult
    {
        $aditivo = $this->modelo->newQuery()->create($atributos);

        return AditivoResult::fromModel($aditivo->refresh()->loadMissing(self::RELACOES_EXIBICAO));
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $aditivoId, array $atributos): AditivoResult
    {
        $aditivo = $this->modelo->newQuery()->findOrFail($aditivoId);
        $aditivo->fill($atributos)->save();

        return AditivoResult::fromModel($aditivo->refresh()->loadMissing(self::RELACOES_EXIBICAO));
    }

    public function excluir(int $aditivoId): void
    {
        $this->modelo->newQuery()->whereKey($aditivoId)->delete();
    }

    public function proximoNumero(int $contratoId): string
    {
        $maior = 0;
        $numeros = $this->modelo->newQuery()->where('contrato_id', $contratoId)->pluck('numero');

        foreach ($numeros as $numero) {
            if (is_string($numero) && preg_match('/ADT (\d+)\//', $numero, $captura) === 1) {
                $maior = max($maior, (int) $captura[1]);
            }
        }

        $ano = substr($this->relogio->hoje(), 0, 4);

        return sprintf('ADT %03d/%s', $maior + 1, $ano);
    }

    /**
     * @param  list<array{papel: string, nome: string}>  $assinaturas
     */
    public function criarAssinaturas(int $aditivoId, array $assinaturas): void
    {
        foreach ($assinaturas as $assinatura) {
            $this->assinatura->newQuery()->create([
                'aditivo_id' => $aditivoId,
                'papel' => $assinatura['papel'],
                'nome' => $assinatura['nome'],
            ]);
        }
    }

    public function marcarAssinatura(int $aditivoId, int $assinaturaId, string $momento): bool
    {
        return $this->assinatura->newQuery()
            ->where('aditivo_id', $aditivoId)
            ->whereKey($assinaturaId)
            ->update([
                'assinado' => true,
                'data_assinatura' => $momento,
            ]) === 1;
    }

    public function todasAsAssinaturasAssinadas(int $aditivoId): bool
    {
        return $this->assinatura->newQuery()
            ->where('aditivo_id', $aditivoId)
            ->where('assinado', false)
            ->doesntExist();
    }
}
