<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;
use App\Shared\Validation\Rules\ValidaCpf;

final class AlterarContratoRequest extends RequisicaoBase
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
            'descontoMensal' => ['sometimes', 'numeric', 'min:0'],
            'multaPorAtraso' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'multaRescisao' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:12'],
            'valorCaucao' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'observacoes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'fiadores' => ['sometimes', 'array'],
            'fiadores.*.nome' => ['required', 'string', 'max:150'],
            'fiadores.*.cpf' => ['required', 'string', 'max:14', new ValidaCpf],
            'fiadores.*.telefone' => ['required', 'string', 'max:30'],
        ];
    }
}
