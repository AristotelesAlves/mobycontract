<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Resources;

use App\Modules\Contratos\DTOs\Output\ParcelaResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ParcelaResult
 */
final class ParcelaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contratoId' => $this->contratoId,
            'referencia' => $this->referencia,
            'vencimento' => $this->vencimento,
            'valor' => $this->valor,
            'juros' => $this->juros,
            'taxasAdicionais' => $this->taxasAdicionais,
            'totalDaParcela' => $this->totalDaParcela,
            'valorPago' => $this->valorPago,
            'dataPagamento' => $this->dataPagamento,
            'status' => $this->status,
            'valorExibido' => $this->valorExibido,
            'pagoEm' => $this->pagoEm,
            'encargos' => $this->encargos,
        ];
    }
}
