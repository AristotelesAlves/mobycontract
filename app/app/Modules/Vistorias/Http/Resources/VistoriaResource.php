<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Resources;

use App\Modules\Vistorias\DTOs\Output\VistoriaResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VistoriaResult $resource
 */
final class VistoriaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'contratoId' => $this->resource->contratoId,
            'numero' => $this->resource->numero,
            'tipo' => $this->resource->tipo,
            'data' => $this->resource->data,
            'vistoriador' => $this->resource->vistoriador,
            'status' => $this->resource->status,
            'podeAlterar' => $this->resource->podeAlterar,
            'podeExcluir' => $this->resource->podeExcluir,
            'observacoes' => $this->resource->observacoes,
            'chaveAcesso' => $this->resource->chaveAcesso,
            'itens' => $this->resource->itens,
            'fotos' => $this->resource->fotos,
            'criadoEm' => $this->resource->criadoEm,
            'atualizadoEm' => $this->resource->atualizadoEm,
            'contratoNumero' => $this->resource->contratoNumero,
            'imovelTitulo' => $this->resource->imovelTitulo,
        ];
    }
}
