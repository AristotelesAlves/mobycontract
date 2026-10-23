<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class RegistrarPagamentoRequest extends RequisicaoBase
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
            'dataPagamento' => ['required', 'date'],
            'valorPago' => ['nullable', 'numeric', 'min:0'],
            'corrigir' => ['sometimes', 'boolean'],
        ];
    }
}
