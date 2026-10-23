<?php

declare(strict_types=1);

namespace App\Modules\Leads\Repositories\Eloquent;

use App\Modules\Leads\Contracts\LeadRepositoryInterface;
use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Modules\Leads\DTOs\Input\ListarLeadsFiltros;
use App\Modules\Leads\DTOs\Output\LeadResult;
use App\Modules\Leads\Models\Lead;
use App\Shared\Application\DTOs\PaginatedResult;

final readonly class LeadRepository implements LeadRepositoryInterface
{
    public function __construct(
        private Lead $modelo,
    ) {}

    /**
     * @return PaginatedResult<LeadResult>
     */
    public function paginar(ListarLeadsFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery();

        if ($filtros->situacao instanceof SituacaoLead) {
            $consulta->where('status', $filtros->situacao->value);
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $termo = '%'.$filtros->texto.'%';
            $digitos = preg_replace('/\D/', '', $filtros->texto) ?? '';

            $consulta->where(function ($subconsulta) use ($termo, $digitos): void {
                $subconsulta
                    ->whereRaw('unaccent(nome) ILIKE ?', [$termo])
                    ->when(
                        $digitos !== '',
                        static fn ($consulta) => $consulta->orWhereRaw("regexp_replace(telefone, '[^0-9]', '', 'g') LIKE ?", ['%'.$digitos.'%']),
                    );
            });
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<LeadResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (Lead $lead): LeadResult => LeadResult::fromModel($lead),
        );

        return $resultado;
    }

    public function buscarPorId(int $leadId): ?LeadResult
    {
        $lead = $this->modelo->newQuery()->find($leadId);

        return $lead === null ? null : LeadResult::fromModel($lead);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): LeadResult
    {
        $criado = $this->modelo->newQuery()->create($atributos);

        return LeadResult::fromModel($criado->refresh());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $leadId, array $atributos): LeadResult
    {
        $lead = $this->modelo->newQuery()->findOrFail($leadId);
        $lead->fill($atributos)->save();

        return LeadResult::fromModel($lead->refresh());
    }

    public function atualizarSituacao(int $leadId, string $situacao): LeadResult
    {
        $lead = $this->modelo->newQuery()->findOrFail($leadId);
        $lead->status = $situacao;
        $lead->save();

        return LeadResult::fromModel($lead->refresh());
    }

    public function excluir(int $leadId): void
    {
        $this->modelo->newQuery()->whereKey($leadId)->delete();
    }
}
