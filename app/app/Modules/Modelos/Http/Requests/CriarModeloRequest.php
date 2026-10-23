<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Http\Requests;

use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Shared\Domain\Enums\Garantia;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class CriarModeloRequest extends RequisicaoBase
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
            'titulo' => ['required', 'string', 'max:200'],
            'tipo' => ['required', Rule::enum(TipoModelo::class)],
            'versao' => ['required', 'integer', 'min:1'],
            'conteudoHtml' => ['present', 'nullable', 'string', 'max:200000'],
            'garantia' => ['nullable', Rule::enum(Garantia::class)],
            'margens' => ['nullable', 'array'],
            'margens.superior' => ['nullable', 'integer'],
            'margens.direita' => ['nullable', 'integer'],
            'margens.inferior' => ['nullable', 'integer'],
            'margens.esquerda' => ['nullable', 'integer'],
            'tipografia' => ['nullable', 'array'],
            'tipografia.corpo' => ['nullable', 'integer'],
            'tipografia.entrelinha' => ['nullable', 'numeric'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'O título é obrigatório.',
            'titulo.max' => 'Título muito longo.',
            'tipo.enum' => 'Selecione o tipo do documento.',
            'versao.min' => 'A versão deve ser maior ou igual a 1.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'tipo' => 'tipo do documento',
            'versao' => 'versão',
            'conteudoHtml' => 'conteúdo',
            'garantia' => 'garantia',
            'margens' => 'margens',
            'tipografia' => 'tipografia',
        ];
    }
}
