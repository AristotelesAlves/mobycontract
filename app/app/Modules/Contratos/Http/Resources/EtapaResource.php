<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Resources;

use App\Modules\Contratos\DTOs\Output\EtapaResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EtapaResult
 */
final class EtapaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'titulo' => $this->titulo,
            'concluida' => $this->concluida,
            'ativa' => $this->ativa,
            'assinaturaId' => $this->assinaturaId,
        ];
    }
}
