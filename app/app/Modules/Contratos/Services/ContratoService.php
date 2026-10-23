<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services;

use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Contracts\ContratoServiceInterface;
use App\Modules\Contratos\Contracts\DividaRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\PrazoVigencia;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\Domain\Enums\StatusDivida;
use App\Modules\Contratos\Domain\Enums\StatusParcela;
use App\Modules\Contratos\Domain\Enums\TipoEncargo;
use App\Modules\Contratos\DTOs\Input\AlterarContratoData;
use App\Modules\Contratos\DTOs\Input\AlterarStatusData;
use App\Modules\Contratos\DTOs\Input\CriarContratoData;
use App\Modules\Contratos\DTOs\Input\LancarEncargoData;
use App\Modules\Contratos\DTOs\Input\ListarContratosFiltros;
use App\Modules\Contratos\DTOs\Input\ListarParcelasFiltros;
use App\Modules\Contratos\DTOs\Input\RegistrarPagamentoData;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\DTOs\Output\EtapaResult;
use App\Modules\Contratos\DTOs\Output\ParcelaResult;
use App\Modules\Contratos\Services\Fluxos\ContratoDadosDerivadosFactory;
use App\Modules\Contratos\Services\Fluxos\CriadorDeSnapshot;
use App\Modules\Contratos\Services\Fluxos\GeradorDeParcelas;
use App\Modules\Imoveis\Contracts\ImovelRepositoryInterface;
use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Modules\Inquilinos\Contracts\InquilinoRepositoryInterface;
use App\Modules\Inquilinos\Domain\ValidadorDeDocumento;
use App\Modules\Inquilinos\DTOs\Input\CriarInquilinoData;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Modules\Modelos\Contracts\ModeloRepositoryInterface;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Modules\Regras\Contracts\RegraImobiliariaRepositoryInterface;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;
use App\Shared\Domain\Enums\Garantia;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

final readonly class ContratoService implements ContratoServiceInterface
{
    public function __construct(
        private ContratoRepositoryInterface $contratos,
        private ImovelRepositoryInterface $imoveis,
        private ModeloRepositoryInterface $modelos,
        private InquilinoRepositoryInterface $inquilinos,
        private GeradorDeParcelas $gerador,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
        private DividaRepositoryInterface $dividas,
        private RegraImobiliariaRepositoryInterface $regras,
        private CriadorDeSnapshot $snapshots,
        private ArmazenadorDeArquivo $arquivos,
        private ContratoDocumentoBlockchainService $documentosBlockchain,
        private ContratoDadosDerivadosFactory $dadosDerivados,
    ) {}

    /**
     * @return PaginatedResult<ContratoResult>
     */
    public function listar(ListarContratosFiltros $filtros): PaginatedResult
    {
        return $this->contratos->paginar($filtros);
    }

    public function obter(int $contratoId): ContratoResult
    {
        $contrato = $this->contratos->buscarPorId($contratoId);

        if (! $contrato instanceof ContratoResult) {
            throw RecursoNaoEncontradoException::contrato($contratoId);
        }

        return $contrato;
    }

    public function armazenar(CriarContratoData $dados, int $autorId): ContratoResult
    {
        return $this->transacao->executar(function () use ($dados, $autorId): ContratoResult {
            $imovel = $this->obterImovel($dados->imovelId);
            $modelo = $this->obterModelo($dados->modeloId);
            $utilidades = $this->dadosDerivados->utilidades($imovel);
            $encargosFixos = $this->dadosDerivados->encargosFixos($imovel);
            /**
             * Criação do locatário (modo "novo") dentro da transação: se qualquer
             * validação falhar, o inquilino recém-criado é revertido e não fica
             * órfão no banco bloqueando a próxima tentativa por CPF duplicado.
             */
            $inquilinoId = $this->resolverInquilino($dados);
            $nomeDoInquilino = $this->nomeDoInquilino($dados, $inquilinoId);

            $this->validarDisponibilidade($dados->imovelId, $inquilinoId);
            $this->validarDesconto($dados, $imovel);
            $this->validarGarantia($dados, $modelo, $inquilinoId);

            $ano = substr($dados->dataInicio, 0, 4);
            $numero = $this->contratos->proximoNumero($ano === '' ? date('Y') : $ano);

            $inquilino = $this->inquilinos->buscarPorId($inquilinoId);
            if (! $inquilino instanceof InquilinoResult) {
                throw RecursoNaoEncontradoException::inquilino($inquilinoId);
            }
            $multaRescisao = $dados->multaRescisao ?? Config::float('mobycontract.contratos.multa_rescisao_padrao', 3.0);
            $contrato = $this->contratos->criar([
                'snapshot' => $this->snapshots->criar($imovel, $inquilino, $modelo, $dados, $multaRescisao),
                'comprovante_garantia' => in_array($this->garantia($modelo), [Garantia::Caucao, Garantia::Fiador], true) ? $dados->comprovanteGarantia : null,
                'comprovante_garantia_nome' => in_array($this->garantia($modelo), [Garantia::Caucao, Garantia::Fiador], true) ? $dados->comprovanteGarantiaNome : null,
                'numero' => $numero,
                'tipo_modelo' => $modelo->tipo,
                'status' => StatusContrato::Rascunho->value,
                'imovel_id' => $imovel->id,
                'inquilino_id' => $inquilinoId,
                'criado_por' => $autorId,
                'modelo_id' => $modelo->id,
                'valor_mensal' => $imovel->valorAluguel,
                'desconto_mensal' => $dados->descontoMensal,
                'multa_por_atraso' => $dados->multaPorAtraso,
                'multa_rescisao' => $multaRescisao,
                'garantia' => $this->garantia($modelo)->value,
                'valor_caucao' => $this->garantia($modelo) === Garantia::Caucao ? $dados->valorCaucao : null,
                'prazo_vigencia' => $dados->prazoVigencia->value,
                'data_inicio' => $dados->dataInicio,
                'data_fim' => $dados->prazoVigencia === PrazoVigencia::Determinado ? $dados->dataFim : null,
                'indice_reajuste' => $dados->indiceReajuste->value,
                ...$this->dadosDerivados->atributosDoImovel($imovel),
                'observacoes' => $dados->observacoes,
                'dia_vencimento_parcela' => $dados->diaVencimentoParcela,
            ]);

            $this->contratos->registrarUtilidades($contrato->id, $utilidades);
            $this->contratos->registrarEncargosFixos($contrato->id, $encargosFixos);

            $this->contratos->criarAssinaturas($contrato->id, $this->dadosDerivados->assinaturas($dados, $nomeDoInquilino, $this->garantia($modelo), $imovel->nomeProprietario));

            if ($this->garantia($modelo) === Garantia::Fiador) {
                $this->contratos->substituirFiadores($contrato->id, $dados->fiadores);
            }

            $this->gerarParcelas($contrato);

            $this->registrarCaucaoComoDivida($contrato);

            $this->registrarHistorico($contrato->id, 'Contrato criado', $autorId);

            $this->eventos->publicar('contrato.criado', [
                'contratoId' => $contrato->id,
                'imovelId' => $imovel->id,
                'autorId' => $autorId,
            ]);

            return $this->obter($contrato->id);
        });
    }

    public function alterar(AlterarContratoData $dados, int $autorId): ContratoResult
    {
        $atual = $this->obter($dados->id);

        if (! $atual->podeAlterar) {
            throw new RegraNegocioException(
                'O contrato não pode ser alterado no status atual.',
                ['status' => 'Somente contratos em rascunho e sem registro em blockchain podem ser alterados.'],
            );
        }

        $atributos = $dados->paraAtributos();
        if (isset($atributos['desconto_mensal']) && $atributos['desconto_mensal'] >= $atual->valorMensal) {
            throw new RegraNegocioException('O desconto não pode ser maior ou igual ao aluguel.', ['descontoMensal' => 'Informe um desconto inferior ao aluguel.']);
        }
        if ($atual->garantia !== Garantia::Caucao->value) {
            unset($atributos['valor_caucao']);
        } elseif (array_key_exists('valor_caucao', $atributos) && ($atributos['valor_caucao'] ?? 0) <= 0) {
            throw new RegraNegocioException('O modelo exige um valor de caução positivo.', ['valorCaucao' => 'Informe o valor da caução.']);
        }

        return $this->transacao->executar(function () use ($dados, $atual, $atributos, $autorId): ContratoResult {
            $contrato = $this->contratos->atualizar($dados->id, $atributos);

            if ($dados->fiadoresInformados() && $atual->garantia === Garantia::Fiador->value) {
                $this->contratos->substituirFiadores($dados->id, $dados->fiadores());
            }

            $this->registrarAlteracoes($atual, $contrato, $autorId);

            $this->eventos->publicar('contrato.alterado', [
                'contratoId' => $contrato->id,
                'autorId' => $autorId,
            ]);

            return $contrato;
        });
    }

    public function alterarStatus(int $contratoId, AlterarStatusData $dados, int $autorId): ContratoResult
    {
        $atual = $this->obter($contratoId);
        $origem = StatusContrato::from($atual->status);

        if (! $origem->podeTransicionarPara($dados->status)) {
            throw TransicaoDeStatusInvalidaException::entre($origem->value, $dados->status->value);
        }

        $this->validarPrecondicoesDeTransicao($origem, $dados->status, $atual);

        return $this->transacao->executar(function () use ($contratoId, $dados, $autorId, $origem): ContratoResult {
            $atributos = ['status' => $dados->status->value];

            if ($dados->status === StatusContrato::Cancelado) {
                $atributos['motivo_cancelamento'] = $dados->motivo;
            }

            $contrato = $this->contratos->atualizar($contratoId, $atributos);

            if ($dados->status === StatusContrato::Vigente) {
                $this->marcarImovelComoAlugado($contratoId);
            }

            if ($dados->status === StatusContrato::Cancelado) {
                $this->contratos->estornarParcelasNaoPagas($contratoId);
                $this->liberarImovel($contratoId);
            }

            if ($dados->status === StatusContrato::Encerrado) {
                $this->liberarImovel($contratoId);
            }

            $this->registrarHistorico(
                $contratoId,
                sprintf('Status alterado de %s para %s', $origem->value, $dados->status->value),
                $autorId,
                $dados->motivo,
            );

            $this->eventos->publicar('contrato.status_alterado', [
                'contratoId' => $contratoId,
                'status' => $dados->status->value,
                'autorId' => $autorId,
            ]);

            return $this->obter($contratoId);
        });
    }

    public function excluir(int $contratoId, int $autorId): void
    {
        $contrato = $this->obter($contratoId);

        if (! $contrato->podeExcluir) {
            throw new RegraNegocioException(
                'O contrato não pode ser excluído no status atual.',
                ['status' => 'Somente contratos em rascunho podem ser excluídos.'],
            );
        }

        $excluido = $this->transacao->executar(function () use ($contratoId, $autorId): bool {
            if (! $this->contratos->excluir($contratoId)) {
                return false;
            }

            $this->eventos->publicar('contrato.excluido', [
                'contratoId' => $contratoId,
                'autorId' => $autorId,
            ]);

            return true;
        });

        $contratoAtual = $excluido ? null : $this->contratos->buscarPorId($contratoId);
        if ($contratoAtual instanceof ContratoResult && ! $contratoAtual->podeExcluir) {
            throw new RegraNegocioException(
                'O contrato não pode ser excluído no status atual.',
                ['status' => 'Somente contratos em rascunho podem ser excluídos.'],
            );
        }

        if (! $excluido) {
            throw RecursoNaoEncontradoException::contrato($contratoId);
        }

        if ($contrato->anexoContrato !== null) {
            $this->arquivos->excluir($contrato->anexoContrato);
        }

        if ($contrato->comprovanteGarantia !== null && $contrato->comprovanteGarantia !== $contrato->anexoContrato) {
            $this->arquivos->excluir($contrato->comprovanteGarantia);
        }
    }

    public function assinar(int $contratoId, int $assinaturaId, int $autorId): ContratoResult
    {
        $atual = $this->obter($contratoId);
        if ($atual->hashBlockchain !== null || in_array($atual->status, ['Encerrado', 'Cancelado'], true)) {
            throw new RegraNegocioException('O conteúdo deste contrato não pode ser alterado. Use um aditivo para registrar uma nova condição.');
        }

        return $this->transacao->executar(function () use ($contratoId, $assinaturaId, $autorId): ContratoResult {
            $assinou = $this->contratos->marcarAssinatura($contratoId, $assinaturaId, now()->toDateTimeString());

            if (! $assinou) {
                throw RecursoNaoEncontradoException::contrato($contratoId);
            }

            $this->registrarHistorico($contratoId, 'Assinatura registrada', $autorId);

            $this->eventos->publicar('contrato.assinado', [
                'contratoId' => $contratoId,
                'assinaturaId' => $assinaturaId,
                'autorId' => $autorId,
            ]);

            return $this->obter($contratoId);
        });
    }

    public function confirmarDocumentoFisico(int $contratoId, ?string $anexo, ?string $nomeAnexo, int $autorId): ContratoResult
    {
        return $this->documentosBlockchain->confirmar($this->obter($contratoId), $anexo, $nomeAnexo, $autorId);
    }

    public function registrarBlockchain(int $contratoId, int $autorId): ContratoResult
    {
        return $this->documentosBlockchain->registrar($this->obter($contratoId), $autorId);
    }

    private function validarPrecondicoesDeTransicao(
        StatusContrato $origem,
        StatusContrato $destino,
        ContratoResult $contrato,
    ): void {
        if ($destino === StatusContrato::Validado) {
            throw new RegraNegocioException(
                'O status Validado é definido pelo registro em blockchain.',
                ['status' => 'Use o registro em blockchain para validar o contrato.'],
            );
        }

        if ($destino === StatusContrato::Cancelado && $contrato->hashBlockchain === null) {
            throw new RegraNegocioException(
                'Um contrato não registrado em blockchain não pode ser cancelado.',
                ['status' => 'Registre o contrato em blockchain antes de cancelá-lo.'],
            );
        }

        if ($origem === StatusContrato::Rascunho && $destino === StatusContrato::Minuta && $this->algumaAssinaturaAssinada($contrato)) {
            throw new RegraNegocioException(
                'Não é possível gerar minuta com assinaturas já registradas.',
                ['status' => 'Nenhuma assinatura pode estar assinada para gerar a minuta.'],
            );
        }

        if ($origem === StatusContrato::Minuta && $destino === StatusContrato::Pendente && ! $this->contratos->possuiAssinatura($contrato->id)) {
            throw new RegraNegocioException(
                'O contrato não possui assinaturas registradas.',
                ['status' => 'Registre as assinaturas antes de tornar o contrato pendente.'],
            );
        }

        if ($origem === StatusContrato::Validado && $destino === StatusContrato::Vigente && ! $this->dataInicioAlcancada($contrato)) {
            throw new RegraNegocioException(
                'O contrato não pode vigorar antes da data de início.',
                ['status' => 'A data de início ainda não foi alcançada.'],
            );
        }

        if ($destino === StatusContrato::Encerrado && ! $this->contratos->possuiAssinatura($contrato->id)) {
            throw new RegraNegocioException(
                'O contrato não possui assinaturas registradas.',
                ['status' => 'É preciso ao menos uma assinatura registrada para encerrar.'],
            );
        }
    }

    private function algumaAssinaturaAssinada(ContratoResult $contrato): bool
    {
        foreach ($contrato->assinaturas as $assinatura) {
            if ($assinatura->assinado) {
                return true;
            }
        }

        return false;
    }

    private function dataInicioAlcancada(ContratoResult $contrato): bool
    {
        return $contrato->dataInicio === null || $contrato->dataInicio <= date('Y-m-d');
    }

    /**
     * @return PaginatedResult<ParcelaResult>
     */
    public function listarParcelas(ListarParcelasFiltros $filtros): PaginatedResult
    {
        if (! $this->contratos->buscarPorId($filtros->contratoId) instanceof ContratoResult) {
            throw RecursoNaoEncontradoException::contrato($filtros->contratoId);
        }

        return $this->contratos->paginarParcelas($filtros);
    }

    public function registrarPagamento(int $contratoId, int $parcelaId, RegistrarPagamentoData $dados, int $autorId): ParcelaResult
    {
        $this->obter($contratoId);

        return $this->transacao->executar(function () use ($contratoId, $parcelaId, $dados, $autorId): ParcelaResult {
            $parcela = $this->obterParcela($contratoId, $parcelaId);

            if (StatusParcela::from($parcela->status)->estaPago() && ! $dados->corrigir) {
                throw new RegraNegocioException(
                    'A parcela já está paga.',
                    ['status' => 'Não é possível registrar o pagamento de uma parcela já paga.'],
                );
            }
            if ($dados->corrigir && (! StatusParcela::from($parcela->status)->estaPago() || $dados->valorPago === null)) {
                throw new RegraNegocioException('Para corrigir, selecione um pagamento já registrado e informe o valor recebido.');
            }
            $atualizada = $this->contratos->atualizarParcela($parcelaId, [
                'status' => StatusParcela::Pago->value,
                'valor_pago' => $dados->valorPago ?? $parcela->totalDaParcela,
                'data_pagamento' => $dados->dataPagamento,
                'pago_em' => $dados->corrigir ? ($parcela->pagoEm ?? now()->toDateTimeString()) : now()->toDateTimeString(),
            ]);

            $this->contratos->registrarHistorico($contratoId, [
                'data' => now()->toDateTimeString(),
                'evento' => $dados->corrigir ? 'Pagamento corrigido' : 'Pagamento registrado',
                'detalhe' => sprintf('Parcela %s: R$ %s pagos em %s', $atualizada->referencia, number_format($atualizada->valorPago, 2, ',', '.'), $dados->dataPagamento)
                    .($dados->corrigir ? sprintf('. Antes: R$ %s em %s', number_format($parcela->valorPago, 2, ',', '.'), $parcela->dataPagamento ?? 'data não informada') : ''),
                'usuario_id' => $autorId,
            ]);

            $this->contratos->registrarAlteracao($contratoId, [
                'data' => now()->toDateTimeString(),
                'usuario_id' => $autorId,
                'acao' => 'Pagamento',
                'campo' => $atualizada->referencia,
                'de' => $dados->corrigir ? sprintf('Pago: R$ %s em %s', number_format($parcela->valorPago, 2, ',', '.'), $parcela->dataPagamento ?? '') : $parcela->status,
                'para' => sprintf('Pago: R$ %s em %s', number_format($atualizada->valorPago, 2, ',', '.'), $dados->dataPagamento),
            ]);

            $this->eventos->publicar($dados->corrigir ? 'contrato.pagamento_corrigido' : 'contrato.pagamento_registrado', [
                'contratoId' => $contratoId,
                'parcelaId' => $parcelaId,
                'autorId' => $autorId,
            ]);

            return $atualizada;
        });
    }

    public function lancarEncargo(int $contratoId, int $parcelaId, LancarEncargoData $dados, int $autorId): ParcelaResult
    {
        $this->obter($contratoId);

        return $this->transacao->executar(function () use ($contratoId, $parcelaId, $dados, $autorId): ParcelaResult {
            $parcela = $this->obterParcela($contratoId, $parcelaId);

            if (StatusParcela::from($parcela->status)->estaPago()) {
                throw new RegraNegocioException(
                    'A parcela já está paga.',
                    ['status' => 'Não é possível lançar encargos em uma parcela já paga.'],
                );
            }

            $atributos = match ($dados->tipo) {
                TipoEncargo::Juros,
                TipoEncargo::Multa => ['juros' => $parcela->juros + $dados->valor],
                TipoEncargo::Taxa => ['taxas_adicionais' => $parcela->taxasAdicionais + $dados->valor],
                TipoEncargo::Desconto => ['valor' => max(0.0, $parcela->valor - $dados->valor)],
            };
            $atualizada = $this->contratos->atualizarParcela($parcelaId, $atributos);

            $this->contratos->registrarEncargo($contratoId, $parcelaId, [
                'tipo' => $dados->tipo->value,
                'valor' => $dados->valor,
                'motivo' => $dados->motivo,
                'criado_por' => $autorId,
            ]);

            $this->eventos->publicar('contrato.encargo_lancado', [
                'contratoId' => $contratoId,
                'parcelaId' => $parcelaId,
                'tipo' => $dados->tipo->value,
                'autorId' => $autorId,
            ]);

            return $atualizada;
        });
    }

    /**
     * @return array<string, int>
     */
    public function totais(): array
    {
        return $this->contratos->totaisPorStatus();
    }

    /**
     * @return list<EtapaResult>
     */
    public function etapas(int $contratoId): array
    {
        $contrato = $this->obter($contratoId);

        $etapas = [];
        $encontrouAtiva = false;

        foreach ($contrato->assinaturas as $assinatura) {
            $concluida = $assinatura->assinado;
            $ativa = ! $concluida && ! $encontrouAtiva;

            if ($ativa) {
                $encontrouAtiva = true;
            }

            $etapas[] = new EtapaResult(
                ordem: count($etapas) + 1,
                titulo: sprintf('Assinatura — %s', $assinatura->papel),
                concluida: $concluida,
                ativa: $ativa,
                assinaturaId: $assinatura->id,
            );
        }

        return $etapas;
    }

    private function obterImovel(int $imovelId): ImovelResult
    {
        $imovel = $this->imoveis->buscarPorId($imovelId);

        if (! $imovel instanceof ImovelResult) {
            throw RecursoNaoEncontradoException::imovel($imovelId);
        }

        return $imovel;
    }

    private function obterModelo(int $modeloId): ModeloResult
    {
        $modelo = $this->modelos->buscarPorId($modeloId);

        if (! $modelo instanceof ModeloResult) {
            throw RecursoNaoEncontradoException::modelo($modeloId);
        }

        if (! $modelo->ativo) {
            throw new RegraNegocioException(
                'O documento modelo está inativo.',
                ['status' => 'Selecione um documento modelo ativo.'],
            );
        }

        if ($modelo->tipo !== 'Contrato') {
            throw new RegraNegocioException(
                'O documento selecionado não é um modelo de contrato.',
                ['status' => 'Selecione um modelo do tipo Contrato.'],
            );
        }

        return $modelo;
    }

    private function resolverInquilino(CriarContratoData $dados): int
    {
        if ($dados->modoInquilino === 'novo' && $dados->inquilino instanceof CriarInquilinoData) {
            return $this->inquilinos->criar($dados->inquilino->paraAtributos())->id;
        }

        if ($dados->modoInquilino === 'novo') {
            throw ValidationException::withMessages(['inquilino' => 'Informe os dados do novo locatário.']);
        }

        if ($dados->inquilinoId === null) {
            throw ValidationException::withMessages(['inquilinoId' => 'Selecione o locatário.']);
        }

        if (! $this->inquilinos->buscarPorId($dados->inquilinoId) instanceof InquilinoResult) {
            throw RecursoNaoEncontradoException::inquilino($dados->inquilinoId);
        }

        return $dados->inquilinoId;
    }

    private function nomeDoInquilino(CriarContratoData $dados, int $inquilinoId): string
    {
        if ($dados->inquilino instanceof CriarInquilinoData) {
            return $dados->inquilino->nome;
        }

        $inquilino = $this->inquilinos->buscarPorId($inquilinoId);

        return $inquilino instanceof InquilinoResult
            ? $inquilino->nome
            : '';
    }

    private function validarDisponibilidade(int $imovelId, int $inquilinoId): void
    {
        $regras = $this->regras->obter();

        if ($regras->contratoUnicoPorImovel && $this->contratos->existeContratoAtivoParaImovel($imovelId)) {
            throw new RegraNegocioException(
                'O imóvel já possui um contrato ativo.',
                ['imovelId' => 'O imóvel já possui um contrato ativo.'],
            );
        }

        if ($regras->contratoUnicoPorInquilino && $this->contratos->existeContratoAtivoParaInquilino($inquilinoId)) {
            throw new RegraNegocioException(
                'O locatário já possui um contrato ativo.',
                ['inquilinoId' => 'O locatário já possui um contrato ativo.'],
            );
        }
    }

    /** Desconto mensal não pode alcançar o aluguel (R-4.5.24). */
    private function validarDesconto(CriarContratoData $dados, ImovelResult $imovel): void
    {
        if ($dados->descontoMensal >= $imovel->valorAluguel) {
            throw new RegraNegocioException(
                'O desconto mensal não pode ser maior ou igual ao valor do aluguel.',
                ['descontoMensal' => 'O desconto mensal não pode ser maior ou igual ao valor do aluguel.'],
            );
        }
    }

    /**
     * Garantias definidas pelo modelo (R-4.5.15, R-4.5.17 a R-4.5.22).
     */
    private function validarGarantia(CriarContratoData $dados, ModeloResult $modelo, int $inquilinoId): void
    {
        $garantia = $this->garantia($modelo);

        if ($garantia === Garantia::Caucao && (($dados->valorCaucao ?? 0.0) <= 0.0)) {
            throw new RegraNegocioException(
                'O modelo exige caução: informe o valor da caução.',
                ['valorCaucao' => 'Informe o valor da caução.'],
            );
        }

        if (in_array($garantia, [Garantia::Caucao, Garantia::Fiador], true) && $dados->comprovanteGarantia === null) {
            throw new RegraNegocioException('Anexe o comprovante da garantia exigida pelo modelo.', ['comprovanteGarantia' => 'Envie o comprovante de depósito ou renda.']);
        }

        if ($garantia !== Garantia::Fiador) {
            return;
        }

        if ($dados->fiadores === []) {
            throw new RegraNegocioException(
                'O modelo exige fiador: informe ao menos um fiador.',
                ['fiadores' => 'Informe ao menos um fiador.'],
            );
        }

        $cpfDoLocatario = $this->cpfDoLocatario($dados, $inquilinoId);

        foreach ($dados->fiadores as $fiador) {
            if ($cpfDoLocatario !== '' && ValidadorDeDocumento::somenteDigitos($fiador->cpf) === $cpfDoLocatario) {
                throw new RegraNegocioException(
                    'O fiador não pode ser o próprio locatário.',
                    ['fiadores' => 'O fiador não pode ser o próprio locatário.'],
                );
            }
        }
    }

    private function cpfDoLocatario(CriarContratoData $dados, int $inquilinoId): string
    {
        if ($dados->inquilino instanceof CriarInquilinoData) {
            return ValidadorDeDocumento::somenteDigitos($dados->inquilino->cpf);
        }

        $inquilino = $this->inquilinos->buscarPorId($inquilinoId);

        return $inquilino instanceof InquilinoResult
            ? ValidadorDeDocumento::somenteDigitos($inquilino->cpf)
            : '';
    }

    private function marcarImovelComoAlugado(int $contratoId): void
    {
        $contrato = $this->obter($contratoId);

        $this->imoveis->atualizarSituacao($contrato->imovelId, SituacaoImovel::Alugado->value);
    }

    private function liberarImovel(int $contratoId): void
    {
        $contrato = $this->obter($contratoId);

        if (! $this->contratos->existeContratoAtivoParaImovel($contrato->imovelId)) {
            $this->imoveis->atualizarSituacao($contrato->imovelId, SituacaoImovel::Disponivel->value);
        }
    }

    private function obterParcela(int $contratoId, int $parcelaId): ParcelaResult
    {
        $parcela = $this->contratos->buscarParcela($contratoId, $parcelaId);

        if (! $parcela instanceof ParcelaResult) {
            throw RecursoNaoEncontradoException::contrato($contratoId);
        }

        return $parcela;
    }

    private function garantia(ModeloResult $modelo): Garantia
    {
        if ($modelo->garantia === null) {
            return Garantia::SemGarantia;
        }

        return Garantia::tryFrom($modelo->garantia) ?? Garantia::SemGarantia;
    }

    /** Caução é registrada como dívida em aberto do locatário (R-4.5.19). */
    private function registrarCaucaoComoDivida(ContratoResult $contrato): void
    {
        if ($contrato->garantia !== Garantia::Caucao->value) {
            return;
        }

        $valorCaucao = $contrato->valorCaucao ?? 0.0;

        if ($valorCaucao <= 0.0) {
            return;
        }

        $this->dividas->criar($contrato->id, [
            'descricao' => 'Caução',
            'valor' => $valorCaucao,
            'status' => StatusDivida::EmAberto->value,
        ]);
    }

    private function gerarParcelas(ContratoResult $contrato): void
    {
        if ($this->contratos->contarParcelas($contrato->id) > 0) {
            return;
        }

        $parcelas = $this->gerador->gerar(
            dataInicio: $contrato->dataInicio ?? date('Y-m-d'),
            dataFim: $contrato->dataFim,
            indeterminado: $contrato->prazoVigencia === PrazoVigencia::Indeterminado->value,
            valorMensal: $contrato->valorMensal,
            descontoMensal: $contrato->descontoMensal,
            diaVencimento: $contrato->diaVencimentoParcela,
        );

        $this->contratos->criarParcelas($contrato->id, $parcelas);
    }

    private function registrarHistorico(int $contratoId, string $evento, int $autorId, ?string $detalhe = null): void
    {
        $this->contratos->registrarHistorico($contratoId, [
            'data' => now()->toDateTimeString(),
            'evento' => $evento,
            'detalhe' => $detalhe,
            'usuario_id' => $autorId,
        ]);
    }

    private function registrarAlteracoes(ContratoResult $de, ContratoResult $para, int $autorId): void
    {
        /** @var array<string, array{string, string|null, string|null}> $campos */
        $campos = [
            'desconto_mensal' => ['Desconto mensal', (string) $de->descontoMensal, (string) $para->descontoMensal],
            'multa_por_atraso' => ['Multa por atraso', (string) $de->multaPorAtraso, (string) $para->multaPorAtraso],
            'multa_rescisao' => ['Multa por rescisão', (string) $de->multaRescisao, (string) $para->multaRescisao],
            'valor_caucao' => ['Valor da caução', $de->valorCaucao === null ? null : (string) $de->valorCaucao, $para->valorCaucao === null ? null : (string) $para->valorCaucao],
            'observacoes' => ['Observações', $de->observacoes, $para->observacoes],
        ];

        foreach ($campos as [$rotulo, $anterior, $novo]) {
            if ($anterior === $novo) {
                continue;
            }

            $this->contratos->registrarAlteracao($de->id, [
                'data' => now()->toDateTimeString(),
                'usuario_id' => $autorId,
                'acao' => 'alteracao',
                'campo' => $rotulo,
                'de' => $anterior,
                'para' => $novo,
            ]);
        }
    }
}
