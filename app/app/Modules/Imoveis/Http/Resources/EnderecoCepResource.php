<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Resources;

use App\Modules\Imoveis\DTOs\Output\EnderecoCepResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EnderecoCepResult
 */
final class EnderecoCepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'logradouro' => $this->logradouro,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'encontrado' => $this->encontrado,
        ];
    }
}
