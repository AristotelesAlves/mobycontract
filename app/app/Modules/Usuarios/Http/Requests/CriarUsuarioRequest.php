<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Requests;

use App\Shared\Domain\Enums\Papel;
use App\Shared\Http\Requests\RequisicaoBase;
use App\Shared\Validation\Rules\SenhaForte;
use Illuminate\Validation\Rule;

final class CriarUsuarioRequest extends RequisicaoBase
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
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'string', 'email:rfc', 'max:180'],
            'senha' => ['required', 'string', 'max:255', SenhaForte::class],
            'papel' => ['required', Rule::enum(Papel::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do usuário.',
            'email.required' => 'Informe o e-mail do usuário.',
            'email.email' => 'Informe um e-mail válido.',
            'senha.required' => 'Informe a senha do usuário.',
            'papel.required' => 'Informe o papel do usuário.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'e-mail',
            'senha' => 'senha',
            'papel' => 'papel',
        ];
    }
}
