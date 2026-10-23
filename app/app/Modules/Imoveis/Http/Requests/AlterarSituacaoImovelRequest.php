<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarSituacaoImovelRequest extends RequisicaoBase
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
            'status' => ['required', Rule::enum(SituacaoImovel::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Informe a situação.',
            'status.enum' => 'Situação inválida.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'situação',
        ];
    }
}
