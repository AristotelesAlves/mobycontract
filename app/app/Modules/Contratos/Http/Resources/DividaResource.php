<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Resources;

use App\Modules\Contratos\DTOs\Output\DividaResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property DividaResult $resource
 */
final class DividaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'contratoId' => $this->resource->contratoId,
            'descricao' => $this->resource->descricao,
            'valor' => $this->resource->valor,
            'status' => $this->resource->status,
            'dataQuitacao' => $this->resource->dataQuitacao,
            'criadoEm' => $this->resource->criadoEm,
        ];
    }
}
