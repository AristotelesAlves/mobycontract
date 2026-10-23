<?php

declare(strict_types=1);

namespace App\Modules\Regras\Http\Resources;

use App\Modules\Regras\DTOs\Output\RegraImobiliariaResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RegraImobiliariaResult $resource
 */
final class RegraResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contratoUnicoPorImovel' => $this->resource->contratoUnicoPorImovel,
            'contratoUnicoPorInquilino' => $this->resource->contratoUnicoPorInquilino,
        ];
    }
}
