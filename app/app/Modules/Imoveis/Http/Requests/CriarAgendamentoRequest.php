<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\DTOs\Input\CriarAgendamentoData;
use App\Shared\Http\Requests\RequisicaoBase;

final class CriarAgendamentoRequest extends RequisicaoBase
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
            'data' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'string', 'max:10'],
            'interessado' => ['required', 'string', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'data.required' => 'Informe a data do agendamento.',
            'data.date_format' => 'Data deve estar no formato AAAA-MM-DD.',
            'hora.required' => 'Informe a hora do agendamento.',
            'interessado.required' => 'Informe o nome do interessado.',
        ];
    }

    public function toDto(int $imovelId): CriarAgendamentoData
    {
        return new CriarAgendamentoData(
            imovelId: $imovelId,
            data: $this->string('data')->toString(),
            hora: $this->string('hora')->toString(),
            interessado: $this->string('interessado')->toString(),
            telefone: $this->filled('telefone') ? $this->string('telefone')->toString() : null,
        );
    }
}
