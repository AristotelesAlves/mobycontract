<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services;

use App\Modules\Contratos\Contracts\AditivoRepositoryInterface;
use App\Modules\Contratos\Contracts\AditivoServiceInterface;
use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\DTOs\Input\AlterarAditivoData;
use App\Modules\Contratos\DTOs\Input\AlterarStatusAditivoData;
use App\Modules\Contratos\DTOs\Input\CriarAditivoData;
use App\Modules\Contratos\DTOs\Input\ListarAditivosFiltros;
use App\Modules\Contratos\DTOs\Output\AditivoResult;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\Services\Fluxos\DerivadorDeDadosExtras;
use App\Modules\Modelos\Contracts\ModeloRepositoryInterface;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\RegistradorBlockchain;
use App\Shared\Contracts\TransactionManager;

final readonly class AditivoService implements AditivoServiceInterface
{
    public function __construct(
        private AditivoRepositoryInterface $aditivos,
        private ContratoRepositoryInterface $contratos,
        private ModeloRepositoryInterface $modelos,
        private DerivadorDeDadosExtras $derivador,
        private RegistradorBlockchain $blockchain,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
    ) {}

    /**
     * @return PaginatedResult<AditivoResult>
     */
    public function listar(ListarAditivosFiltros $filtros): PaginatedResult
    {
        $this->obterContrato($filtros->contratoId);

        return $this->aditivos->paginar($filtros);
    }

    public function obter(int $aditivoId): AditivoResult
    {
        $aditivo = $this->aditivos->buscarPorId($aditivoId);

        if (! $aditivo instanceof AditivoResult) {
            throw RecursoNaoEncontradoException::contrato($aditivoId);
        }

        return $aditivo;
    }

    public function armazenar(CriarAditivoData $dados, int $autorId): AditivoResult
    {
        $contrato = $this->obterContrato($dados->contratoId);
        $modelo = $this->obterModelo($dados->modeloId);

        $this->validarContratoAditavel($contrato);

        if ($dados->motivos === []) {
            throw new RegraNegocioException(
                'Selecione ao menos um motivo para o aditivo.',
                ['motivos' => 'Informe pelo menos um motivo.'],
            );
        }

        return $this->transacao->executar(function () use ($dados, $contrato, $modelo, $autorId): AditivoResult {
            $numero = $this->aditivos->proximoNumero($contrato->id);

            $aditivo = $this->aditivos->criar([
                'snapshot' => [
                    'versao' => 1, 'capturadoEm' => now()->toIso8601String(),
                    'contratoSnapshotDisponivel' => $contrato->snapshot !== null,
                    'modelo' => ['titulo' => $modelo->titulo, 'versao' => $modelo->versao, 'conteudo_html' => $modelo->conteudoHtml],
                    'descricaoInicial' => $dados->descricao,
                    'dadosExtrasIniciais' => $this->derivador->derivar($dados->motivos, $dados->camposDosMotivos()),
                ],
                'contrato_id' => $contrato->id,
                'numero' => $numero,
                'titulo' => $dados->titulo,
                'data' => $dados->data ?? date('Y-m-d'),
                'descricao' => $dados->descricao,
                'criado_por' => $autorId,
                'status' => StatusAditivo::Rascunho->value,
                'motivos' => array_map(static fn (MotivoAditivo $motivo): string => $motivo->value, $dados->motivos),
                'modelo_id' => $modelo->id,
                'modelo_titulo' => $modelo->titulo,
                'dados_extras' => $this->derivador->derivar($dados->motivos, $dados->camposDosMotivos()),
                'upload_confirmado' => false,
                'status_blockchain' => 'Aguardando registro',
            ]);

            $this->aditivos->criarAssinaturas($aditivo->id, $this->assinaturasDoContrato($contrato));

            $aditivo = $this->aditivos->buscarPorId($aditivo->id) ?? $aditivo;

            $this->contratos->registrarHistorico($contrato->id, [
                'data' => now()->toDateTimeString(),
                'evento' => 'Aditivo criado',
                'detalhe' => $numero,
                'usuario_id' => $autorId,
            ]);

            $this->eventos->publicar('aditivo.criado', [
                'aditivoId' => $aditivo->id,
                'contratoId' => $contrato->id,
                'autorId' => $autorId,
            ]);

            return $aditivo;
        });
    }

    public function alterar(AlterarAditivoData $dados, int $autorId): AditivoResult
    {
        $aditivo = $this->obter($dados->id);

        if (! StatusAditivo::from($aditivo->status)->permiteEdicao()) {
            throw new RegraNegocioException(
                'O aditivo não pode ser alterado no status atual.',
                ['status' => 'Aditivos assinados, validados ou cancelados não podem ser editados.'],
            );
        }

        $atributos = $dados->paraAtributos();

        if ($dados->motivosInformados()) {
            $atributos['motivos'] = array_map(static fn (MotivoAditivo $motivo): string => $motivo->value, $dados->motivos());
            $atributos['dados_extras'] = $this->derivador->derivar($dados->motivos(), $dados->camposDosMotivos());
        }

        return $this->transacao->executar(function () use ($dados, $atributos, $autorId): AditivoResult {
            $aditivo = $this->aditivos->atualizar($dados->id, $atributos);

            $this->eventos->publicar('aditivo.alterado', [
                'aditivoId' => $aditivo->id,
                'autorId' => $autorId,
            ]);

            return $aditivo;
        });
    }

    public function alterarStatus(int $aditivoId, AlterarStatusAditivoData $dados, int $autorId): AditivoResult
    {
        $atual = $this->obter($aditivoId);
        $origem = StatusAditivo::from($atual->status);

        if ($dados->status === StatusAditivo::Validado) {
            throw new RegraNegocioException(
                'O status Validado é definido pelo registro em blockchain.',
                ['status' => 'Use o registro em blockchain para validar o aditivo.'],
            );
        }

        if (! $origem->podeTransicionarPara($dados->status)) {
            throw TransicaoDeStatusInvalidaException::entre($origem->value, $dados->status->value);
        }

        if ($dados->status === StatusAditivo::Assinado && ! $this->aditivos->todasAsAssinaturasAssinadas($aditivoId)) {
            throw new RegraNegocioException(
                'O aditivo ainda possui assinaturas pendentes.',
                ['status' => 'Todas as partes precisam assinar antes de marcar como assinado.'],
            );
        }

        return $this->transacao->executar(function () use ($aditivoId, $dados, $autorId): AditivoResult {
            $atributos = ['status' => $dados->status->value];

            if ($dados->status === StatusAditivo::Cancelado) {
                $atributos['motivo_cancelamento'] = $dados->motivo;
            }

            $aditivo = $this->aditivos->atualizar($aditivoId, $atributos);

            $this->eventos->publicar('aditivo.status_alterado', [
                'aditivoId' => $aditivoId,
                'status' => $dados->status->value,
                'autorId' => $autorId,
            ]);

            return $aditivo;
        });
    }

    public function assinar(int $aditivoId, int $assinaturaId, int $autorId): AditivoResult
    {
        $aditivo = $this->obter($aditivoId);
        if ($aditivo->hashBlockchain !== null || $aditivo->status === 'Cancelado') {
            throw new RegraNegocioException('O conteúdo deste aditivo não pode ser alterado.');
        }

        return $this->transacao->executar(function () use ($aditivoId, $assinaturaId, $autorId): AditivoResult {
            $assinou = $this->aditivos->marcarAssinatura($aditivoId, $assinaturaId, now()->toDateTimeString());

            if (! $assinou) {
                throw RecursoNaoEncontradoException::contrato($aditivoId);
            }

            $this->eventos->publicar('aditivo.assinado', [
                'aditivoId' => $aditivoId,
                'assinaturaId' => $assinaturaId,
                'autorId' => $autorId,
            ]);

            return $this->obter($aditivoId);
        });
    }

    public function confirmarDocumento(int $aditivoId, ?string $anexo, ?string $nomeAnexo, int $autorId): AditivoResult
    {
        $aditivo = $this->obter($aditivoId);
        if ($aditivo->hashBlockchain !== null || $aditivo->status === 'Cancelado') {
            throw new RegraNegocioException('O conteúdo deste aditivo não pode ser alterado.');
        }

        $atualizado = $this->aditivos->atualizar($aditivo->id, [
            'upload_confirmado' => true,
            ...($anexo !== null ? ['anexo_nome' => $nomeAnexo, 'anexo' => $anexo] : []),
        ]);

        $this->eventos->publicar('aditivo.documento_confirmado', [
            'aditivoId' => $aditivoId,
            'autorId' => $autorId,
        ]);

        return $atualizado;
    }

    public function registrarBlockchain(int $aditivoId, int $autorId): AditivoResult
    {
        $aditivo = $this->obter($aditivoId);
        $status = StatusAditivo::from($aditivo->status);

        if ($aditivo->hashBlockchain !== null) {
            throw new RegraNegocioException(
                'O aditivo já foi registrado em blockchain.',
                ['statusBlockchain' => 'O registro é irreversível e ocorre uma única vez.'],
            );
        }

        if ($status !== StatusAditivo::Assinado) {
            throw TransicaoDeStatusInvalidaException::entre($status->value, StatusAditivo::Validado->value);
        }

        if (! $aditivo->uploadConfirmado) {
            throw new RegraNegocioException(
                'O comprovante físico ainda não foi confirmado.',
                ['uploadConfirmado' => 'Confirme o documento físico antes do registro.'],
            );
        }

        if (! $this->aditivos->todasAsAssinaturasAssinadas($aditivoId)) {
            throw new RegraNegocioException(
                'O aditivo ainda possui assinaturas pendentes.',
                ['assinaturas' => 'Todas as partes precisam assinar antes do registro.'],
            );
        }

        $hash = $this->blockchain->registrar($this->conteudoBlockchain($aditivo));

        return $this->transacao->executar(function () use ($aditivoId, $hash, $autorId): AditivoResult {
            $aditivo = $this->aditivos->atualizar($aditivoId, [
                'status' => StatusAditivo::Validado->value,
                'hash_blockchain' => $hash,
                'status_blockchain' => 'Registrado',
                'data_registro_blockchain' => now()->toDateTimeString(),
            ]);

            $this->eventos->publicar('aditivo.blockchain_registrado', [
                'aditivoId' => $aditivoId,
                'hash' => $hash,
                'autorId' => $autorId,
            ]);

            return $aditivo;
        });
    }

    public function excluir(int $aditivoId, int $autorId): void
    {
        $aditivo = $this->obter($aditivoId);

        if (! StatusAditivo::from($aditivo->status)->permiteExclusao()) {
            throw new RegraNegocioException(
                'O aditivo não pode ser excluído no status atual.',
                ['status' => 'Somente aditivos em rascunho podem ser excluídos.'],
            );
        }

        $this->transacao->executar(function () use ($aditivo, $autorId): void {
            $this->aditivos->excluir($aditivo->id);

            $this->eventos->publicar('aditivo.excluido', [
                'aditivoId' => $aditivo->id,
                'contratoId' => $aditivo->contratoId,
                'autorId' => $autorId,
            ]);
        });
    }

    private function obterContrato(int $contratoId): ContratoResult
    {
        $contrato = $this->contratos->buscarPorId($contratoId);

        if (! $contrato instanceof ContratoResult) {
            throw RecursoNaoEncontradoException::contrato($contratoId);
        }

        return $contrato;
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
                ['modeloId' => 'Selecione um documento modelo ativo.'],
            );
        }

        if ($modelo->tipo !== 'Aditivo') {
            throw new RegraNegocioException(
                'O documento selecionado não é um modelo de aditivo.',
                ['modeloId' => 'Selecione um modelo do tipo Aditivo.'],
            );
        }

        return $modelo;
    }

    private function validarContratoAditavel(ContratoResult $contrato): void
    {
        $status = StatusContrato::from($contrato->status);

        if ($status === StatusContrato::Encerrado || $status === StatusContrato::Cancelado) {
            throw new RegraNegocioException(
                'O contrato não permite aditivo no status atual.',
                ['contratoId' => 'Contratos encerrados ou cancelados não podem receber aditivo.'],
            );
        }
    }

    /**
     * @return list<array{papel: string, nome: string}>
     */
    private function assinaturasDoContrato(ContratoResult $contrato): array
    {
        $assinaturas = [];

        foreach ($contrato->assinaturas as $assinatura) {
            $assinaturas[] = ['papel' => $assinatura->papel, 'nome' => $assinatura->nome];
        }

        return $assinaturas;
    }

    private function conteudoBlockchain(AditivoResult $aditivo): string
    {
        return implode('|', [
            'aditivo',
            (string) $aditivo->id,
            (string) $aditivo->contratoId,
            $aditivo->numero,
            $aditivo->titulo,
        ]);
    }
}
