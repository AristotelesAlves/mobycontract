<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Resources;

use App\Modules\Leads\DTOs\Output\LeadResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LeadResult
 */
final class LeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'imovelDeInteresse' => $this->imovelDeInteresse,
            'origem' => $this->origem,
            'situacao' => $this->situacao,
            'observacoes' => $this->observacoes,
            'dataCadastro' => $this->dataCadastro,
            'convertidoEm' => $this->convertidoEm,
        ];
    }
}
