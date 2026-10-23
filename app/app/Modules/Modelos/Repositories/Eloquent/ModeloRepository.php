<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Repositories\Eloquent;

use App\Modules\Modelos\Contracts\ModeloRepositoryInterface;
use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\DTOs\Input\ListarModelosFiltros;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Modules\Modelos\Models\Modelo;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\SequenciaNumericaInterface;

final readonly class ModeloRepository implements ModeloRepositoryInterface
{
    public function __construct(
        private Modelo $modelo,
        private SequenciaNumericaInterface $sequencia,
    ) {}

    /**
     * @return PaginatedResult<ModeloResult>
     */
    public function paginar(ListarModelosFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery()->withExists('contratos');

        if ($filtros->tipo instanceof TipoModelo) {
            $consulta->where('tipo', $filtros->tipo->value);
        }

        if ($filtros->ativo !== null) {
            $consulta->where('status', $filtros->ativo);
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $consulta->whereRaw('unaccent(titulo) ILIKE ?', ['%'.$filtros->texto.'%']);
        }

        $pagina = $consulta
            ->orderBy($filtros->ordenarPor, $filtros->direcao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<ModeloResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (Modelo $modelo): ModeloResult => ModeloResult::fromModel($modelo),
        );

        return $resultado;
    }

    public function buscarPorId(int $modeloId): ?ModeloResult
    {
        $modelo = $this->modelo->newQuery()->withExists('contratos')->find($modeloId);

        return $modelo === null ? null : ModeloResult::fromModel($modelo);
    }

    public function proximoCodigo(): int
    {
        return $this->sequencia->proximo('modelo_codigo');
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): ModeloResult
    {
        $criado = $this->modelo->newQuery()->create($atributos);

        return ModeloResult::fromModel($criado->refresh());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $modeloId, array $atributos): ModeloResult
    {
        $modelo = $this->modelo->newQuery()->lockForUpdate()->findOrFail($modeloId);
        if ($modelo->contratos()->exists()) {
            throw new RegraNegocioException('Este modelo está vinculado a um contrato e não pode ser editado. Clone o modelo para criar uma nova versão.');
        }
        $modelo->fill($atributos)->save();

        return ModeloResult::fromModel($modelo->refresh());
    }

    public function definirAtivo(int $modeloId, bool $ativo): ModeloResult
    {
        $modelo = $this->modelo->newQuery()->findOrFail($modeloId);
        $modelo->status = $ativo;
        $modelo->save();

        return ModeloResult::fromModel($modelo->refresh());
    }
}
