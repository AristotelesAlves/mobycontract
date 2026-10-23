<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Resources;

use App\Modules\Imoveis\DTOs\Output\AnotacaoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AnotacaoResult
 */
final class AnotacaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'imovelId' => $this->imovelId,
            'texto' => $this->texto,
            'autorId' => $this->autorId,
            'autorNome' => $this->autorNome,
            'criadoEm' => $this->criadoEm,
        ];
    }
}
