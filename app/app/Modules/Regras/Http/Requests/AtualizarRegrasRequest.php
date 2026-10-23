<?php

declare(strict_types=1);

namespace App\Modules\Regras\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class AtualizarRegrasRequest extends RequisicaoBase
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
            'contratoUnicoPorImovel' => ['required', 'boolean'],
            'contratoUnicoPorInquilino' => ['required', 'boolean'],
        ];
    }
}
