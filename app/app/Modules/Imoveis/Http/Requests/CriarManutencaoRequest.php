<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\DTOs\Input\CriarManutencaoData;
use App\Shared\Http\Requests\RequisicaoBase;

final class CriarManutencaoRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'responsavel' => ['nullable', 'string', 'max:150'],
            'custo' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'Informe o título da manutenção.',
            'custo.min' => 'O custo da manutenção não pode ser negativo.',
        ];
    }

    public function toDto(int $imovelId): CriarManutencaoData
    {
        return new CriarManutencaoData(
            imovelId: $imovelId,
            titulo: $this->string('titulo')->toString(),
            descricao: $this->filled('descricao') ? $this->string('descricao')->toString() : null,
            responsavel: $this->filled('responsavel') ? $this->string('responsavel')->toString() : null,
            custo: $this->filled('custo') ? $this->float('custo') : null,
        );
    }
}
