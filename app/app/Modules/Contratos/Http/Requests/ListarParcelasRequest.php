<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\StatusParcela;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

final class ListarParcelasRequest extends RequisicaoBase
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
            'ordenarPor' => $this->input('ordenarPor', 'vencimento'),
            'direcao' => $this->input('direcao', 'asc'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(StatusParcela::class)],
            'texto' => ['nullable', 'string', 'max:200'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'tamanhoPagina' => ['nullable', 'integer', 'min:1', 'max:'.$this->tamanhoMaximoPagina()],
            'ordenarPor' => ['nullable', Rule::in(['referencia', 'vencimento', 'valor', 'status'])],
            'direcao' => ['nullable', Rule::in(['asc', 'desc'])],
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
