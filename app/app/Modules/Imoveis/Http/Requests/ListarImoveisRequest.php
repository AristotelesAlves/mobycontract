<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

final class ListarImoveisRequest extends RequisicaoBase
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
            'ordenarPor' => $this->input('ordenarPor', 'titulo'),
            'direcao' => $this->input('direcao', 'asc'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SituacaoImovel::class)],
            'tipo' => ['nullable', Rule::enum(TipoImovel::class)],
            'texto' => ['nullable', 'string', 'max:150'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'tamanhoPagina' => ['nullable', 'integer', 'min:1', 'max:'.$this->tamanhoMaximoPagina()],
            'ordenarPor' => ['nullable', Rule::in(['titulo', 'tipo', 'status', 'area', 'valor_aluguel', 'created_at'])],
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
            'tipo' => 'tipo',
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
