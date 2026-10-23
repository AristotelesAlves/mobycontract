<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Requests;

use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

final class ListarLeadsRequest extends RequisicaoBase
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
            'ordenarPor' => $this->input('ordenarPor', 'data_cadastro'),
            'direcao' => $this->input('direcao', 'desc'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SituacaoLead::class)],
            'texto' => ['nullable', 'string', 'max:150'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'tamanhoPagina' => ['nullable', 'integer', 'min:1', 'max:'.$this->tamanhoMaximoPagina()],
            'ordenarPor' => ['nullable', Rule::in(['nome', 'telefone', 'status', 'origem', 'data_cadastro'])],
            'direcao' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'situação',
            'texto' => 'texto de busca',
            'ordenarPor' => 'campo de ordenação',
            'direcao' => 'direção da ordenação',
        ];
    }

    private function itensPorPaginaPadrao(): int
    {
        return Config::integer('mobycontract.listagem.itens_por_pagina', 20);
    }

    private function tamanhoMaximoPagina(): int
    {
        return Config::integer('mobycontract.listagem.tamanho_maximo_pagina', 100);
    }
}
