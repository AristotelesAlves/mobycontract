<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Requests;

use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use Illuminate\Validation\Rule;

final class AlterarInquilinoRequest extends RequisicaoDeInquilino
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'cpf' => ['sometimes', 'required', 'string', $this->regraDeDocumento(), Rule::unique('inquilinos', 'cpf')->ignore($id)],
            'tipoPessoa' => ['sometimes', 'nullable', Rule::enum(TipoPessoa::class)],
            'rg' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'required', 'email', 'max:180', Rule::unique('inquilinos', 'email')->ignore($id)],
            'telefone' => ['sometimes', 'required', 'string', 'max:30'],
            'dataNascimento' => ['sometimes', 'required', 'date_format:Y-m-d', 'before_or_equal:'.$this->dataLimiteDeIdade()],
            'estadoCivil' => ['sometimes', 'required', Rule::enum(EstadoCivil::class)],
            'profissao' => ['sometimes', 'nullable', 'string', 'max:120'],
            'renda' => ['sometimes', 'required', 'numeric', 'min:0'],
            'endereco' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
