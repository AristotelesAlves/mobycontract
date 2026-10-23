<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Resources;

use App\Modules\Imoveis\DTOs\Output\InteressadoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InteressadoResult
 */
final class InteressadoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'imovelId' => $this->imovelId,
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'criadoEm' => $this->criadoEm,
        ];
    }
}
