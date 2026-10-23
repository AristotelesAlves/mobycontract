<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class CriarVistoriaRequest extends RequisicaoBase
{
    private const array TIPOS = ['Entrada', 'Retirada', 'Periódica'];

    private const array ESTADOS = ['Ótimo', 'Bom', 'Regular', 'Ruim', 'Substituir'];

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
            'tipo' => ['required', 'string', 'in:'.implode(',', self::TIPOS)],
            'data' => ['required', 'date_format:Y-m-d'],
            'vistoriador' => ['nullable', 'string', 'max:120'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'itens' => ['nullable', 'array', 'max:100'],
            'itens.*.ambiente' => ['required', 'string', 'max:80'],
            'itens.*.item' => ['required', 'string', 'max:120'],
            'itens.*.estado' => ['required', 'string', 'in:'.implode(',', self::ESTADOS)],
            'itens.*.observacao' => ['nullable', 'string', 'max:500'],
        ];
    }
}
