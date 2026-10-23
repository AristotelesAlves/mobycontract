<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Repositories\Eloquent;

use App\Modules\Imoveis\Contracts\ImovelRepositoryInterface;
use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Modules\Imoveis\DTOs\Input\ListarImoveisFiltros;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Modules\Imoveis\Models\Imovel;
use App\Shared\Application\DTOs\PaginatedResult;

final readonly class ImovelRepository implements ImovelRepositoryInterface
{
    public function __construct(
        private Imovel $modelo,
    ) {}

    /**
     * @return PaginatedResult<ImovelResult>
     */
    public function paginar(ListarImoveisFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery();

        if ($filtros->situacao instanceof SituacaoImovel) {
            $consulta->where('status', $filtros->situacao->value);
        }

        if ($filtros->tipo instanceof TipoImovel) {
            $consulta->where('tipo', $filtros->tipo->value);
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $termo = '%'.$filtros->texto.'%';
            $consulta->whereRaw('unaccent(titulo) ILIKE ?', [$termo]);
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<ImovelResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (Imovel $imovel): ImovelResult => ImovelResult::fromModel($imovel),
        );

        return $resultado;
    }

    public function buscarPorId(int $imovelId): ?ImovelResult
    {
        $imovel = $this->modelo->newQuery()->find($imovelId);

        return $imovel === null ? null : ImovelResult::fromModel($imovel);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): ImovelResult
    {
        $criado = $this->modelo->newQuery()->create($atributos);

        return ImovelResult::fromModel($criado->refresh());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $imovelId, array $atributos): ImovelResult
    {
        $imovel = $this->modelo->newQuery()->findOrFail($imovelId);
        $imovel->fill($atributos)->save();

        return ImovelResult::fromModel($imovel->refresh());
    }

    public function atualizarSituacao(int $imovelId, string $situacao): ImovelResult
    {
        $imovel = $this->modelo->newQuery()->findOrFail($imovelId);
        $imovel->status = $situacao;
        $imovel->save();

        return ImovelResult::fromModel($imovel->refresh());
    }

    public function excluir(int $imovelId): void
    {
        $this->modelo->newQuery()->whereKey($imovelId)->delete();
    }
}
