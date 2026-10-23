<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Http\Resources;

use App\Modules\Modelos\Domain\AnaliseDeVariaveis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AnaliseDeVariaveis
 */
final class AnaliseDeConteudoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'variaveisEncontradas' => $this->encontradas,
            'variaveisInvalidas' => $this->invalidas,
            'variaveisDesconhecidas' => $this->desconhecidas,
        ];
    }
}
