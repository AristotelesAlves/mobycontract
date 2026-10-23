<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class ConfirmarDocumentoRequest extends RequisicaoBase
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
            'anexo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:25600'],
            'nomeAnexo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
