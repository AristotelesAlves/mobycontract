<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Resources;

use App\Modules\Inquilinos\DTOs\Output\IndicadoresInquilinoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IndicadoresInquilinoResult
 */
final class IndicadoresInquilinoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'idade' => $this->idade,
            'possuiContratoAtivo' => $this->possuiContratoAtivo,
            'compromissoRenda' => $this->compromissoRenda,
            'limiteSeguro' => $this->limiteSeguro,
            'alertaCompromisso' => $this->alertaCompromisso,
            'contratosVigentes' => $this->contratosVigentes,
            'contratosEncerrados' => $this->contratosEncerrados,
            'parcelasAtrasadas' => $this->parcelasAtrasadas,
        ];
    }
}
