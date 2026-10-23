<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\StatusInteressado;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoInteressadoData;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarSituacaoInteressadoRequest extends RequisicaoBase
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
            'status' => ['required', Rule::enum(StatusInteressado::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Informe a situação do interessado.',
            'status.enum' => 'Situação de interessado inválida.',
        ];
    }

    public function toDto(): AlterarSituacaoInteressadoData
    {
        return new AlterarSituacaoInteressadoData(
            status: $this->string('status')->toString(),
        );
    }
}
