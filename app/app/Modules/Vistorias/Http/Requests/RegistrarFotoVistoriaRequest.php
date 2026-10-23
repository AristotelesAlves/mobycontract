<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

/**
 * A foto pode chegar de duas formas: arquivo binário (multipart) ou
 * metadados JSON (nomeAnexo). Uma das duas é obrigatória.
 */
final class RegistrarFotoVistoriaRequest extends RequisicaoBase
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
            'descricao' => ['nullable', 'string', 'max:120'],
            'nomeAnexo' => ['nullable', 'string', 'max:200', 'required_without:anexo'],
            'anexo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
