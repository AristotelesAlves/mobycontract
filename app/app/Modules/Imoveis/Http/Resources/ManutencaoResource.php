<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Resources;

use App\Modules\Imoveis\DTOs\Output\ManutencaoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ManutencaoResult
 */
final class ManutencaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'imovelId' => $this->imovelId,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'responsavel' => $this->responsavel,
            'custo' => $this->custo,
            'status' => $this->status,
            'criadoEm' => $this->criadoEm,
        ];
    }
}
