<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class ValidarConteudoRequest extends RequisicaoBase
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
            'conteudoHtml' => ['present', 'nullable', 'string', 'max:200000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'conteudoHtml' => 'conteúdo',
        ];
    }
}
