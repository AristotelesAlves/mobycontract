<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\TipoEncargo;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class LancarEncargoRequest extends RequisicaoBase
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
            'tipo' => ['required', Rule::enum(TipoEncargo::class)],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }
}
