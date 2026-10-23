<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class AlterarStatusVistoriaRequest extends RequisicaoBase
{
    private const array STATUS = ['Agendada', 'Em andamento', 'Concluída', 'Cancelada'];

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
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUS)],
            'motivo' => ['nullable', 'string', 'min:10', 'max:500'],
        ];
    }
}
