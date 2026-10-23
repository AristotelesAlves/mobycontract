<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class CriarAditivoRequest extends RequisicaoBase
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
            'titulo' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'data' => ['nullable', 'date'],
            'modeloId' => ['required', 'integer', 'min:1'],
            'motivos' => ['required', 'array', 'min:1'],
            'motivos.*' => ['string', Rule::enum(MotivoAditivo::class)],
            'novoValor' => ['nullable', 'numeric', 'min:0'],
            'novaDataFim' => ['nullable', 'date'],
            'clausulaModificada' => ['nullable', 'string', 'max:5000'],
            'novaTaxaCondominio' => ['nullable', 'numeric', 'min:0'],
            'novoIPTU' => ['nullable', 'numeric', 'min:0'],
            'novoSeguroIncendio' => ['nullable', 'numeric', 'min:0'],
            'novoAgua' => ['nullable', 'numeric', 'min:0'],
            'novoEnergia' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
