<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;
use App\Shared\Validation\Rules\SenhaForte;

final class LoginRequest extends RequisicaoBase
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
            'email' => ['required', 'string', 'email:rfc', 'max:180'],
            'senha' => ['required', 'string', 'max:255', SenhaForte::class],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'senha.required' => 'Informe a senha.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'e-mail',
            'senha' => 'senha',
        ];
    }
}
