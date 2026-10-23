<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Services;

use App\Modules\Imoveis\Contracts\ImovelRepositoryInterface;
use App\Modules\Imoveis\Contracts\ImovelServiceInterface;
use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\DTOs\Input\AlterarImovelData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoImovelData;
use App\Modules\Imoveis\DTOs\Input\CriarImovelData;
use App\Modules\Imoveis\DTOs\Input\ListarImoveisFiltros;
use App\Modules\Imoveis\DTOs\Output\CustoMensalResult;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Modules\Imoveis\DTOs\Output\OcupacaoResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;

final readonly class ImovelService implements ImovelServiceInterface
{
    public function __construct(
        private ImovelRepositoryInterface $imoveis,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
    ) {}

    /**
     * @return PaginatedResult<ImovelResult>
     */
    public function listar(ListarImoveisFiltros $filtros): PaginatedResult
    {
        return $this->imoveis->paginar($filtros);
    }

    public function obter(int $imovelId): ImovelResult
    {
        $imovel = $this->imoveis->buscarPorId($imovelId);

        if (! $imovel instanceof ImovelResult) {
            throw RecursoNaoEncontradoException::imovel($imovelId);
        }

        return $imovel;
    }

    public function armazenar(CriarImovelData $dados, int $autorId): ImovelResult
    {
        return $this->transacao->executar(function () use ($dados, $autorId): ImovelResult {
            $imovel = $this->imoveis->criar([
                ...$dados->paraAtributos(),
                'status' => SituacaoImovel::Disponivel->value,
            ]);

            $this->eventos->publicar('imovel.criado', [
                'imovelId' => $imovel->id,
                'autorId' => $autorId,
            ]);

            return $imovel;
        });
    }

    public function alterar(AlterarImovelData $dados, int $autorId): ImovelResult
    {
        $atual = $this->obter($dados->id);

        return $this->transacao->executar(function () use ($dados, $atual, $autorId): ImovelResult {
            $imovel = $this->imoveis->atualizar($dados->id, $dados->paraAtributosSobre($atual));

            $this->eventos->publicar('imovel.alterado', [
                'imovelId' => $imovel->id,
                'autorId' => $autorId,
            ]);

            return $imovel;
        });
    }

    public function alterarSituacao(AlterarSituacaoImovelData $dados, int $autorId): ImovelResult
    {
        if (! $dados->situacao->controladaPeloUsuario()) {
            throw new RegraNegocioException(
                'A disponibilidade do imóvel é derivada dos contratos.',
                ['status' => 'A situação escolhida não pode ser definida manualmente.'],
            );
        }

        $atual = $this->obter($dados->id);
        $situacaoAtual = SituacaoImovel::from($atual->situacao);

        if (! $situacaoAtual->podeTransicionarPara($dados->situacao)) {
            throw TransicaoDeStatusInvalidaException::entre($situacaoAtual->value, $dados->situacao->value);
        }

        return $this->transacao->executar(function () use ($dados, $autorId): ImovelResult {
            $imovel = $this->imoveis->atualizarSituacao($dados->id, $dados->situacao->value);

            $this->eventos->publicar('imovel.situacao_alterada', [
                'imovelId' => $imovel->id,
                'situacao' => $imovel->situacao,
                'autorId' => $autorId,
            ]);

            return $imovel;
        });
    }

    public function excluir(int $imovelId, int $autorId): void
    {
        $this->obter($imovelId);

        $this->transacao->executar(function () use ($imovelId, $autorId): void {
            $this->imoveis->excluir($imovelId);

            $this->eventos->publicar('imovel.excluido', [
                'imovelId' => $imovelId,
                'autorId' => $autorId,
            ]);
        });
    }

    public function custoMensal(int $imovelId): CustoMensalResult
    {
        $imovel = $this->obter($imovelId);

        $valorCondominio = $imovel->valorCondominio ?? 0.0;
        $valorIptuMensal = ($imovel->valorIptu ?? 0.0) / 12;

        return new CustoMensalResult(
            custoMensal: $imovel->valorAluguel + $valorCondominio + $valorIptuMensal,
            valorAluguel: $imovel->valorAluguel,
            valorCondominio: $valorCondominio,
            valorIptuMensal: $valorIptuMensal,
        );
    }

    public function ocupacao(int $imovelId): OcupacaoResult
    {
        $imovel = $this->obter($imovelId);

        return new OcupacaoResult(
            situacao: $imovel->situacao,
            contratoAtivo: null,
        );
    }
}
