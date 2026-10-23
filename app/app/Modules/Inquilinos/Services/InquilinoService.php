<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Services;

use App\Modules\Inquilinos\Contracts\InquilinoRepositoryInterface;
use App\Modules\Inquilinos\Contracts\InquilinoServiceInterface;
use App\Modules\Inquilinos\DTOs\Input\AlterarInquilinoData;
use App\Modules\Inquilinos\DTOs\Input\CriarInquilinoData;
use App\Modules\Inquilinos\DTOs\Input\ListarInquilinosFiltros;
use App\Modules\Inquilinos\DTOs\Output\IndicadoresInquilinoResult;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;
use Illuminate\Support\Facades\Config;

final readonly class InquilinoService implements InquilinoServiceInterface
{
    public function __construct(
        private InquilinoRepositoryInterface $inquilinos,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
    ) {}

    /**
     * @return PaginatedResult<InquilinoResult>
     */
    public function listar(ListarInquilinosFiltros $filtros): PaginatedResult
    {
        return $this->inquilinos->paginar($filtros);
    }

    public function obter(int $inquilinoId): InquilinoResult
    {
        $inquilino = $this->inquilinos->buscarPorId($inquilinoId);

        if (! $inquilino instanceof InquilinoResult) {
            throw RecursoNaoEncontradoException::inquilino($inquilinoId);
        }

        return $inquilino;
    }

    public function armazenar(CriarInquilinoData $dados, int $autorId): InquilinoResult
    {
        return $this->transacao->executar(function () use ($dados, $autorId): InquilinoResult {
            $inquilino = $this->inquilinos->criar($dados->paraAtributos());

            $this->eventos->publicar('inquilino.criado', [
                'inquilinoId' => $inquilino->id,
                'autorId' => $autorId,
            ]);

            return $inquilino;
        });
    }

    public function alterar(AlterarInquilinoData $dados, int $autorId): InquilinoResult
    {
        $atual = $this->obter($dados->id);

        return $this->transacao->executar(function () use ($dados, $atual, $autorId): InquilinoResult {
            $inquilino = $this->inquilinos->atualizar($dados->id, $dados->paraAtributosSobre($atual));

            $this->eventos->publicar('inquilino.alterado', [
                'inquilinoId' => $inquilino->id,
                'autorId' => $autorId,
            ]);

            return $inquilino;
        });
    }

    public function excluir(int $inquilinoId, int $autorId): void
    {
        $this->obter($inquilinoId);

        $this->transacao->executar(function () use ($inquilinoId, $autorId): void {
            $this->inquilinos->excluir($inquilinoId);

            $this->eventos->publicar('inquilino.removido', [
                'inquilinoId' => $inquilinoId,
                'autorId' => $autorId,
            ]);
        });
    }

    public function indicadores(int $inquilinoId): IndicadoresInquilinoResult
    {
        $inquilino = $this->obter($inquilinoId);

        $percentualSeguro = Config::integer('mobycontract.inquilinos.limite_compromisso_seguro', 30);

        return new IndicadoresInquilinoResult(
            idade: $inquilino->idade,
            possuiContratoAtivo: false,
            compromissoRenda: null,
            limiteSeguro: $inquilino->renda * ($percentualSeguro / 100),
            alertaCompromisso: false,
            contratosVigentes: 0,
            contratosEncerrados: 0,
            parcelasAtrasadas: 0,
        );
    }
}
