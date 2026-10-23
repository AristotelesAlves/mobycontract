<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\StatusManutencao;
use App\Modules\Imoveis\DTOs\Input\AlterarManutencaoData;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarManutencaoRequest extends RequisicaoBase
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
            'titulo' => ['nullable', 'string', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'responsavel' => ['nullable', 'string', 'max:150'],
            'custo' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::enum(StatusManutencao::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'custo.min' => 'O custo da manutenção não pode ser negativo.',
            'status.enum' => 'Situação de manutenção inválida.',
        ];
    }

    public function toDto(): AlterarManutencaoData
    {
        return new AlterarManutencaoData(
            titulo: $this->filled('titulo') ? $this->string('titulo')->toString() : null,
            descricao: $this->filled('descricao') ? $this->string('descricao')->toString() : null,
            responsavel: $this->filled('responsavel') ? $this->string('responsavel')->toString() : null,
            custo: $this->filled('custo') ? $this->float('custo') : null,
            status: $this->filled('status') ? $this->string('status')->toString() : null,
        );
    }
}
