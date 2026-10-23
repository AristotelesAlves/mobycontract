<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarStatusAditivoRequest extends RequisicaoBase
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
            'status' => ['required', Rule::enum(StatusAditivo::class)],
            'motivo' => [
                'nullable',
                'string',
                'min:10',
                'max:250',
                Rule::requiredIf($this->input('status') === StatusAditivo::Cancelado->value),
            ],
        ];
    }
}
