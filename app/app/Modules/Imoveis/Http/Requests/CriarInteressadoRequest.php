<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\TipoInteressado;
use App\Modules\Imoveis\DTOs\Input\CriarInteressadoData;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class CriarInteressadoRequest extends RequisicaoBase
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
            'nome' => ['required', 'string', 'max:150'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'tipo' => ['required', Rule::enum(TipoInteressado::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do interessado.',
            'telefone.required' => 'Informe o telefone do interessado.',
            'tipo.required' => 'Informe o tipo de interesse.',
            'tipo.enum' => 'Tipo de interesse inválido.',
        ];
    }

    public function toDto(int $imovelId): CriarInteressadoData
    {
        return new CriarInteressadoData(
            imovelId: $imovelId,
            nome: $this->string('nome')->toString(),
            telefone: $this->string('telefone')->toString(),
            email: $this->filled('email') ? $this->string('email')->toString() : null,
            tipo: $this->string('tipo')->toString(),
        );
    }
}
