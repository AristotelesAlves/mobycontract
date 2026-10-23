<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Http\Requests;

use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

final class ListarModelosRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'pagina' => $this->input('pagina', 1),
            'tamanhoPagina' => $this->input('tamanhoPagina', $this->itensPorPaginaPadrao()),
            'ordenarPor' => $this->input('ordenarPor', 'codigo'),
            'direcao' => $this->input('direcao', 'asc'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['nullable', Rule::enum(TipoModelo::class)],
            'ativo' => ['nullable', 'boolean'],
            'texto' => ['nullable', 'string', 'max:200'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'tamanhoPagina' => ['nullable', 'integer', 'min:1', 'max:'.$this->tamanhoMaximoPagina()],
            'ordenarPor' => ['nullable', Rule::in(['codigo', 'titulo', 'tipo', 'versao', 'created_at'])],
            'direcao' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tipo' => 'tipo',
            'ativo' => 'situação',
            'texto' => 'texto de busca',
            'ordenarPor' => 'campo de ordenação',
            'direcao' => 'direção da ordenação',
        ];
    }

    private function tamanhoMaximoPagina(): int
    {
        return Config::integer('mobycontract.listagem.tamanho_maximo_pagina', 100);
    }

    private function itensPorPaginaPadrao(): int
    {
        return Config::integer('mobycontract.listagem.itens_por_pagina', 20);
    }
}
