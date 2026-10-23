<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;
use App\Shared\Validation\Rules\SenhaForte;

final class AlterarSenhaDeUsuarioRequest extends RequisicaoBase
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
            'novaSenha' => ['required', 'string', 'max:255', SenhaForte::class],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'novaSenha.required' => 'Informe a nova senha.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'novaSenha' => 'nova senha',
        ];
    }
}
