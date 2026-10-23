<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Requests;

use App\Shared\Domain\Enums\Papel;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarUsuarioRequest extends RequisicaoBase
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
            'nome' => ['sometimes', 'string', 'min:3', 'max:150'],
            'email' => ['sometimes', 'string', 'email:rfc', 'max:180'],
            'papel' => ['sometimes', Rule::enum(Papel::class)],
            'ativo' => ['sometimes', 'boolean'],
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
            'papel' => 'papel',
            'ativo' => 'situação ativa',
        ];
    }
}
