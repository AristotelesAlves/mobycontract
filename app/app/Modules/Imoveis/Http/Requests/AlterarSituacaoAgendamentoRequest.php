<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\StatusAgendamento;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoAgendamentoData;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarSituacaoAgendamentoRequest extends RequisicaoBase
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
            'status' => ['required', Rule::enum(StatusAgendamento::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Informe a situação do agendamento.',
            'status.enum' => 'Situação de agendamento inválida.',
        ];
    }

    public function toDto(): AlterarSituacaoAgendamentoData
    {
        return new AlterarSituacaoAgendamentoData(
            status: $this->string('status')->toString(),
        );
    }
}
