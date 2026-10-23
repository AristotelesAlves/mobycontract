<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

final class ListarAditivosRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'pagina' => $this->input('pagina', 1),
            'tamanhoPagina' => $this->input('tamanhoPagina', Config::integer('mobycontract.listagem.itens_por_pagina', 20)),
            'ordenarPor' => $this->input('ordenarPor', 'created_at'),
            'direcao' => $this->input('direcao', 'desc'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(StatusAditivo::class)],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'tamanhoPagina' => ['nullable', 'integer', 'min:1', 'max:'.Config::integer('mobycontract.listagem.tamanho_maximo_pagina', 100)],
            'ordenarPor' => ['nullable', Rule::in(['numero', 'data', 'status', 'created_at'])],
            'direcao' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
