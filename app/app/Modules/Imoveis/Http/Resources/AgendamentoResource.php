<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Resources;

use App\Modules\Imoveis\DTOs\Output\AgendamentoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AgendamentoResult
 */
final class AgendamentoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'imovelId' => $this->imovelId,
            'data' => $this->data,
            'hora' => $this->hora,
            'interessado' => $this->interessado,
            'telefone' => $this->telefone,
            'status' => $this->status,
            'criadoEm' => $this->criadoEm,
        ];
    }
}
