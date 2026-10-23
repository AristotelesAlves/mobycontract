<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services;

use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\PrazoVigencia;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\Services\Fluxos\GeradorDeParcelas;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\RegistradorBlockchain;
use App\Shared\Contracts\TransactionManager;

/** Coordena a confirmação de documento e a âncora imutável em blockchain. */
final readonly class ContratoDocumentoBlockchainService
{
    public function __construct(
        private ContratoRepositoryInterface $contratos,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
        private RegistradorBlockchain $blockchain,
        private ArmazenadorDeArquivo $arquivos,
        private GeradorDeParcelas $geradorDeParcelas,
    ) {}

    public function confirmar(ContratoResult $contrato, ?string $anexo, ?string $nomeAnexo, int $autorId): ContratoResult
    {
        $this->garantirConteudoEditavel($contrato);

        return $this->transacao->executar(function () use ($contrato, $anexo, $nomeAnexo, $autorId): ContratoResult {
            $atualizado = $this->contratos->atualizar($contrato->id, [
                'documento_fisico_confirmado' => true,
                ...($anexo !== null ? ['anexo_contrato' => $anexo, 'anexo_contrato_nome' => $nomeAnexo] : []),
            ]);
            $this->registrarHistorico($contrato->id, 'Documento físico confirmado', $autorId);
            $this->eventos->publicar('contrato.documento_confirmado', ['contratoId' => $contrato->id, 'autorId' => $autorId]);

            return $atualizado;
        });
    }

    public function registrar(ContratoResult $contrato, int $autorId): ContratoResult
    {
        $this->validarPrecondicoesDeRegistro($contrato);
        $hashDocumento = $this->hashDoDocumento($contrato);
        $hashTransacao = $this->blockchain->registrar($this->conteudoDoRegistro($contrato).'|'.$hashDocumento);

        return $this->transacao->executar(function () use ($contrato, $hashDocumento, $hashTransacao, $autorId): ContratoResult {
            $atualizado = $this->contratos->atualizar($contrato->id, [
                'status' => StatusContrato::Validado->value,
                'hash_blockchain' => $hashTransacao,
                'documento_hash_blockchain' => $hashDocumento,
                'status_blockchain' => 'Registrado',
                'data_registro_blockchain' => now()->toDateTimeString(),
            ]);
            $this->gerarParcelasQuandoNecessario($atualizado);
            $this->registrarHistorico($contrato->id, 'Registro em blockchain', $autorId);
            $this->eventos->publicar('contrato.blockchain_registrado', ['contratoId' => $contrato->id, 'hash' => $hashTransacao, 'autorId' => $autorId]);

            return $atualizado;
        });
    }

    private function validarPrecondicoesDeRegistro(ContratoResult $contrato): void
    {
        if ($contrato->hashBlockchain !== null) {
            throw new RegraNegocioException('O contrato já foi registrado em blockchain.', ['statusBlockchain' => 'O registro em blockchain é irreversível e ocorre uma única vez.']);
        }
        if (! StatusContrato::from($contrato->status)->podeTransicionarPara(StatusContrato::Validado)) {
            throw TransicaoDeStatusInvalidaException::entre($contrato->status, StatusContrato::Validado->value);
        }
        if (! $this->contratos->todasAsAssinaturasAssinadas($contrato->id)) {
            throw new RegraNegocioException('O contrato ainda possui assinaturas pendentes.', ['assinaturas' => 'Todas as partes precisam assinar antes do registro.']);
        }
        if (! $contrato->documentoFisicoConfirmado) {
            throw new RegraNegocioException('O comprovante físico ainda não foi confirmado.', ['documentoFisicoConfirmado' => 'Confirme o documento físico antes do registro.']);
        }
        if ($contrato->anexoContrato === null) {
            throw new RegraNegocioException('Anexe o contrato assinado antes de registrar na blockchain.', ['anexo' => 'O arquivo assinado é necessário para verificar a integridade posteriormente.']);
        }
    }

    private function garantirConteudoEditavel(ContratoResult $contrato): void
    {
        if ($contrato->hashBlockchain === null && ! in_array($contrato->status, [StatusContrato::Encerrado->value, StatusContrato::Cancelado->value], true)) {
            return;
        }

        throw new RegraNegocioException('O conteúdo deste contrato não pode ser alterado. Use um aditivo para registrar uma nova condição.');
    }

    private function hashDoDocumento(ContratoResult $contrato): string
    {
        $conteudo = $this->arquivos->ler((string) $contrato->anexoContrato);
        if ($conteudo === '') {
            throw new RegraNegocioException('O arquivo do contrato está vazio. Anexe o documento assinado.');
        }

        return '0x'.hash('sha256', $conteudo);
    }

    private function conteudoDoRegistro(ContratoResult $contrato): string
    {
        return implode('|', ['contrato', (string) $contrato->id, $contrato->numero, $contrato->imovelTitulo, $contrato->inquilinoNome, $contrato->dataInicio ?? '', $contrato->dataFim ?? '', number_format($contrato->valorMensal, 2, '.', '')]);
    }

    private function gerarParcelasQuandoNecessario(ContratoResult $contrato): void
    {
        if ($this->contratos->contarParcelas($contrato->id) > 0) {
            return;
        }

        $this->contratos->criarParcelas($contrato->id, $this->geradorDeParcelas->gerar(
            dataInicio: $contrato->dataInicio ?? date('Y-m-d'),
            dataFim: $contrato->dataFim,
            indeterminado: $contrato->prazoVigencia === PrazoVigencia::Indeterminado->value,
            valorMensal: $contrato->valorMensal,
            descontoMensal: $contrato->descontoMensal,
            diaVencimento: $contrato->diaVencimentoParcela,
        ));
    }

    private function registrarHistorico(int $contratoId, string $evento, int $autorId): void
    {
        $this->contratos->registrarHistorico($contratoId, ['data' => now()->toDateTimeString(), 'evento' => $evento, 'detalhe' => null, 'usuario_id' => $autorId]);
    }
}
