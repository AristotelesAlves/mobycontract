<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarStatusRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        if ($this->input('status') !== StatusContrato::Cancelado->value) {
            return true;
        }

        $usuario = $this->user();

        return $usuario instanceof User && $usuario->enumPapel() === Papel::Administrador;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusContrato::class)],
            'motivo' => [
                'nullable',
                'string',
                'min:10',
                'max:250',
                Rule::requiredIf($this->input('status') === StatusContrato::Cancelado->value),
            ],
        ];
    }
}
