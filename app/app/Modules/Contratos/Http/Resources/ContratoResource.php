<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Resources;

use App\Modules\Contratos\DTOs\Output\ContratoAssinaturaResult;
use App\Modules\Contratos\DTOs\Output\ContratoFiadorResult;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContratoResult
 */
final class ContratoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'snapshot' => $request->routeIs('contratos.listar') ? null : $this->snapshot,
            'historicoPreservado' => $this->snapshot !== null,
            'historico' => $this->historico,
            'id' => $this->id,
            'numero' => $this->numero,
            'tipoModelo' => $this->tipoModelo,
            'status' => $this->status,
            'situacaoEfetiva' => $this->situacaoEfetiva,
            'podeAlterar' => $this->podeAlterar,
            'podeExcluir' => $this->podeExcluir,
            'imovelId' => $this->imovelId,
            'imovelTitulo' => $this->imovelTitulo,
            'imovelEndereco' => $this->imovelEndereco,
            'imovelCidade' => $this->imovelCidade,
            'imovelMatricula' => $this->imovelMatricula,
            'imovelArea' => $this->imovelArea,
            'imovelQuartos' => $this->imovelQuartos,
            'imovelBanheiros' => $this->imovelBanheiros,
            'imovelVagas' => $this->imovelVagas,
            'inquilinoId' => $this->inquilinoId,
            'inquilinoNome' => $this->inquilinoNome,
            'criadoPor' => $this->criadoPor,
            'modeloId' => $this->modeloId,
            'modeloTitulo' => $this->modeloTitulo,
            'modeloVersao' => $this->modeloVersao,
            'valorMensal' => $this->valorMensal,
            'descontoMensal' => $this->descontoMensal,
            'multaPorAtraso' => $this->multaPorAtraso,
            'multaRescisao' => $this->multaRescisao,
            'garantia' => $this->garantia,
            'valorCaucao' => $this->valorCaucao,
            'fiadores' => array_map(
                static fn (ContratoFiadorResult $fiador): array => [
                    'id' => $fiador->id,
                    'nome' => $fiador->nome,
                    'cpf' => $fiador->cpf,
                    'telefone' => $fiador->telefone,
                ],
                $this->fiadores,
            ),
            'destinacao' => $this->destinacao,
            'prazoVigencia' => $this->prazoVigencia,
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'indiceReajuste' => $this->indiceReajuste,
            'responsabilidadeIptu' => $this->responsabilidadeIptu,
            'valorEstimadoIptu' => $this->valorEstimadoIptu,
            'responsabilidadeCondominio' => $this->responsabilidadeCondominio,
            'valorEstimadoCondominio' => $this->valorEstimadoCondominio,
            'consumoAgua' => $this->consumoAgua,
            'consumoLuz' => $this->consumoLuz,
            'consumoGas' => $this->consumoGas,
            'consumoInternet' => $this->consumoInternet,
            'anexoContratoNome' => $this->anexoContratoNome,
            'observacoes' => $this->observacoes,
            'motivoCancelamento' => $this->motivoCancelamento,
            'documentoFisicoConfirmado' => $this->documentoFisicoConfirmado,
            'diaVencimentoParcela' => $this->diaVencimentoParcela,
            'hashBlockchain' => $this->hashBlockchain,
            'dataRegistroBlockchain' => $this->dataRegistroBlockchain,
            'statusBlockchain' => $this->statusBlockchain,
            'criadoEm' => $this->criadoEm,
            'atualizadoEm' => $this->atualizadoEm,
            'assinaturas' => array_map(
                static fn (ContratoAssinaturaResult $assinatura): array => [
                    'id' => $assinatura->id,
                    'papel' => $assinatura->papel,
                    'nome' => $assinatura->nome,
                    'assinado' => $assinatura->assinado,
                    'dataAssinatura' => $assinatura->dataAssinatura,
                ],
                $this->assinaturas,
            ),
        ];
    }
}
