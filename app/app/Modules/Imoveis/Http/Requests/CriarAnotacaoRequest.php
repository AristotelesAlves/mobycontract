<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\DTOs\Input\CriarAnotacaoData;
use App\Shared\Http\Requests\RequisicaoBase;

final class CriarAnotacaoRequest extends RequisicaoBase
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
            'texto' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'texto.required' => 'Informe o texto da anotação.',
            'texto.min' => 'Texto da anotação não pode ser vazio.',
            'texto.max' => 'Texto da anotação muito longo.',
        ];
    }

    public function toDto(int $imovelId): CriarAnotacaoData
    {
        return new CriarAnotacaoData(
            imovelId: $imovelId,
            texto: $this->string('texto')->toString(),
        );
    }
}
