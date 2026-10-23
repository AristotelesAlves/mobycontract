<?php

declare(strict_types=1);

namespace App\Modules\Leads\Services;

use App\Modules\Inquilinos\Contracts\InquilinoServiceInterface;
use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use App\Modules\Inquilinos\DTOs\Input\CriarInquilinoData;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Modules\Leads\Contracts\LeadRepositoryInterface;
use App\Modules\Leads\Contracts\LeadServiceInterface;
use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Modules\Leads\DTOs\Input\AlterarLeadData;
use App\Modules\Leads\DTOs\Input\AlterarSituacaoLeadData;
use App\Modules\Leads\DTOs\Input\ConverterLeadData;
use App\Modules\Leads\DTOs\Input\CriarLeadData;
use App\Modules\Leads\DTOs\Input\ListarLeadsFiltros;
use App\Modules\Leads\DTOs\Output\LeadResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;

final readonly class LeadService implements LeadServiceInterface
{
    public function __construct(
        private LeadRepositoryInterface $leads,
        private InquilinoServiceInterface $inquilinos,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
    ) {}

    /**
     * @return PaginatedResult<LeadResult>
     */
    public function listar(ListarLeadsFiltros $filtros): PaginatedResult
    {
        return $this->leads->paginar($filtros);
    }

    public function obter(int $leadId): LeadResult
    {
        $lead = $this->leads->buscarPorId($leadId);

        if (! $lead instanceof LeadResult) {
            throw RecursoNaoEncontradoException::lead($leadId);
        }

        return $lead;
    }

    public function armazenar(CriarLeadData $dados, int $autorId): LeadResult
    {
        return $this->transacao->executar(function () use ($dados, $autorId): LeadResult {
            $lead = $this->leads->criar([
                ...$dados->paraAtributos(),
                'status' => SituacaoLead::Novo->value,
            ]);

            $this->eventos->publicar('lead.criado', [
                'leadId' => $lead->id,
                'autorId' => $autorId,
            ]);

            return $lead;
        });
    }

    public function alterar(AlterarLeadData $dados, int $autorId): LeadResult
    {
        $atual = $this->obter($dados->id);

        return $this->transacao->executar(function () use ($dados, $atual, $autorId): LeadResult {
            $lead = $this->leads->atualizar($dados->id, $dados->paraAtributosSobre($atual));

            $this->eventos->publicar('lead.alterado', [
                'leadId' => $lead->id,
                'autorId' => $autorId,
            ]);

            return $lead;
        });
    }

    public function alterarSituacao(AlterarSituacaoLeadData $dados, int $autorId): LeadResult
    {
        $atual = $this->obter($dados->id);
        $situacaoAtual = SituacaoLead::from($atual->situacao);

        if (! $situacaoAtual->podeTransicionarPara($dados->situacao)) {
            throw TransicaoDeStatusInvalidaException::entre($situacaoAtual->value, $dados->situacao->value);
        }

        return $this->transacao->executar(function () use ($dados, $autorId): LeadResult {
            $lead = $this->leads->atualizarSituacao($dados->id, $dados->situacao->value);

            $this->eventos->publicar('lead.situacao_alterada', [
                'leadId' => $lead->id,
                'situacao' => $lead->situacao,
                'autorId' => $autorId,
            ]);

            return $lead;
        });
    }

    public function excluir(int $leadId, int $autorId): void
    {
        $this->obter($leadId);

        $this->transacao->executar(function () use ($leadId, $autorId): void {
            $this->leads->excluir($leadId);

            $this->eventos->publicar('lead.excluido', [
                'leadId' => $leadId,
                'autorId' => $autorId,
            ]);
        });
    }

    public function converter(int $leadId, ConverterLeadData $dados, int $autorId): InquilinoResult
    {
        $lead = $this->obter($leadId);
        $situacaoAtual = SituacaoLead::from($lead->situacao);

        if (! $situacaoAtual->podeTransicionarPara(SituacaoLead::Alugou)) {
            throw TransicaoDeStatusInvalidaException::entre($situacaoAtual->value, SituacaoLead::Alugou->value);
        }

        return $this->transacao->executar(function () use ($leadId, $dados, $autorId): InquilinoResult {
            $locatario = $this->inquilinos->armazenar(
                new CriarInquilinoData(
                    nome: $dados->nome,
                    cpf: $dados->cpf,
                    tipoPessoa: TipoPessoa::Pf,
                    rg: $dados->rg,
                    email: $dados->email,
                    telefone: $dados->telefone,
                    dataNascimento: $dados->dataNascimento,
                    estadoCivil: EstadoCivil::from($dados->estadoCivil),
                    profissao: $dados->profissao,
                    renda: $dados->renda,
                    endereco: $dados->endereco,
                    clienteId: $leadId,
                ),
                $autorId,
            );

            $this->leads->atualizarSituacao($leadId, SituacaoLead::Alugou->value);

            $this->eventos->publicar('lead.convertido', [
                'leadId' => $leadId,
                'inquilinoId' => $locatario->id,
                'autorId' => $autorId,
            ]);

            return $locatario;
        });
    }
}
