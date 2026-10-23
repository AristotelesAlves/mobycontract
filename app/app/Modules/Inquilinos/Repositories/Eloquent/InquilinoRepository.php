<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Repositories\Eloquent;

use App\Modules\Inquilinos\Contracts\InquilinoRepositoryInterface;
use App\Modules\Inquilinos\Domain\Enums\FiltroDeContrato;
use App\Modules\Inquilinos\DTOs\Input\ListarInquilinosFiltros;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Modules\Inquilinos\Models\Inquilino;
use App\Shared\Application\DTOs\PaginatedResult;

final readonly class InquilinoRepository implements InquilinoRepositoryInterface
{
    public function __construct(
        private Inquilino $modelo,
    ) {}

    /**
     * @return PaginatedResult<InquilinoResult>
     */
    public function paginar(ListarInquilinosFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery();

        if ($filtros->situacao === FiltroDeContrato::ComContrato) {
            $consulta->whereRaw('1 = 0');
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $termo = '%'.$filtros->texto.'%';
            $digitos = preg_replace('/\D/', '', $filtros->texto) ?? '';

            $consulta->where(function ($subconsulta) use ($termo, $digitos): void {
                $subconsulta
                    ->whereRaw('unaccent(nome) ILIKE ?', [$termo])
                    ->when(
                        $digitos !== '',
                        static fn ($consulta) => $consulta->orWhere('cpf', 'like', '%'.$digitos.'%'),
                    );
            });
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<InquilinoResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (Inquilino $inquilino): InquilinoResult => InquilinoResult::fromModel($inquilino),
        );

        return $resultado;
    }

    public function buscarPorId(int $inquilinoId): ?InquilinoResult
    {
        $inquilino = $this->modelo->newQuery()->find($inquilinoId);

        return $inquilino === null ? null : InquilinoResult::fromModel($inquilino);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): InquilinoResult
    {
        $criado = $this->modelo->newQuery()->create($atributos);

        return InquilinoResult::fromModel($criado->refresh());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $inquilinoId, array $atributos): InquilinoResult
    {
        $inquilino = $this->modelo->newQuery()->findOrFail($inquilinoId);
        $inquilino->fill($atributos)->save();

        return InquilinoResult::fromModel($inquilino->refresh());
    }

    public function excluir(int $inquilinoId): void
    {
        $this->modelo->newQuery()->whereKey($inquilinoId)->delete();
    }
}
