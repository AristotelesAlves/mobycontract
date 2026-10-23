<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Resources;

use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InquilinoResult
 */
final class InquilinoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'tipoPessoa' => $this->tipoPessoa,
            'rg' => $this->rg,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'dataNascimento' => $this->dataNascimento,
            'estadoCivil' => $this->estadoCivil,
            'profissao' => $this->profissao,
            'renda' => $this->renda,
            'endereco' => $this->endereco,
            'clienteId' => $this->clienteId,
            'dataCadastro' => $this->dataCadastro,
            'idade' => $this->idade,
            'contratoAtivo' => $this->contratoAtivo,
        ];
    }
}
