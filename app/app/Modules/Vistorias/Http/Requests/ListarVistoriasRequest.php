<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class ListarVistoriasRequest extends RequisicaoBase
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
            'status' => ['nullable', 'string', 'in:'.implode(',', self::STATUS)],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'tamanhoPagina' => ['nullable', 'integer', 'min:1', 'max:100'],
            'ordenarPor' => ['nullable', 'string', 'in:data,created_at,status'],
            'direcao' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
