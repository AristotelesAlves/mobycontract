<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Resources;

use App\Modules\Contratos\DTOs\Output\AditivoAssinaturaResult;
use App\Modules\Contratos\DTOs\Output\AditivoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AditivoResult
 */
final class AditivoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'snapshot' => $this->snapshot,
            'id' => $this->id,
            'contratoId' => $this->contratoId,
            'numero' => $this->numero,
            'titulo' => $this->titulo,
            'data' => $this->data,
            'descricao' => $this->descricao,
            'criadoPor' => $this->criadoPor,
            'status' => $this->status,
            'podeAlterar' => $this->podeAlterar,
            'podeExcluir' => $this->podeExcluir,
            'motivos' => $this->motivos,
            'modeloId' => $this->modeloId,
            'modeloTitulo' => $this->modeloTitulo,
            'dadosExtras' => $this->dadosExtras,
            'uploadConfirmado' => $this->uploadConfirmado,
            'anexoNome' => $this->anexoNome,
            'hashBlockchain' => $this->hashBlockchain,
            'dataRegistroBlockchain' => $this->dataRegistroBlockchain,
            'statusBlockchain' => $this->statusBlockchain,
            'criadoEm' => $this->criadoEm,
            'atualizadoEm' => $this->atualizadoEm,
            'assinaturas' => array_map(
                static fn (AditivoAssinaturaResult $assinatura): array => [
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
