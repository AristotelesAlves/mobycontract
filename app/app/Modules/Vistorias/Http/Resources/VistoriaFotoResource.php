<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Resources;

use App\Modules\Vistorias\DTOs\Output\VistoriaFotoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VistoriaFotoResult $resource
 */
final class VistoriaFotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'vistoriaId' => $this->resource->vistoriaId,
            'descricao' => $this->resource->descricao,
            'nomeAnexo' => $this->resource->nomeAnexo,
            'anexo' => $this->resource->anexo,
        ];
    }
}
