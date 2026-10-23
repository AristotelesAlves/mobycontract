<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Requests;

use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use Illuminate\Validation\Rule;

final class CriarInquilinoRequest extends RequisicaoDeInquilino
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'cpf' => ['required', 'string', $this->regraDeDocumento(), Rule::unique('inquilinos', 'cpf')],
            'tipoPessoa' => ['nullable', Rule::enum(TipoPessoa::class)],
            'rg' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:180', Rule::unique('inquilinos', 'email')],
            'telefone' => ['required', 'string', 'max:30'],
            'dataNascimento' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$this->dataLimiteDeIdade()],
            'estadoCivil' => ['required', Rule::enum(EstadoCivil::class)],
            'profissao' => ['nullable', 'string', 'max:120'],
            'renda' => ['required', 'numeric', 'min:0'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'clienteId' => ['nullable', 'integer', 'exists:leads,id'],
        ];
    }
}
