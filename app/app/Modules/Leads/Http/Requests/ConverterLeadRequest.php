<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Requests;

use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Http\Requests\RegrasDeDocumento;
use App\Modules\Leads\DTOs\Input\ConverterLeadData;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class ConverterLeadRequest extends RequisicaoBase
{
    use RegrasDeDocumento;

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
            'nome' => ['required', 'string', 'max:150'],
            'cpf' => ['required', 'string', $this->regraDeDocumento()],
            'email' => ['required', 'email', 'max:150'],
            'telefone' => ['required', 'string', 'max:30'],
            'dataNascimento' => ['required', 'date_format:Y-m-d', 'before_or_equal:now -18 years'],
            'estadoCivil' => ['required', Rule::enum(EstadoCivil::class)],
            'renda' => ['required', 'numeric', 'min:0'],
            'endereco' => ['required', 'string', 'max:255'],
            'rg' => ['nullable', 'string', 'max:30'],
            'profissao' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do locatário.',
            'cpf.required' => 'Informe o CPF do locatário.',
            'email.required' => 'Informe o e-mail do locatário.',
            'telefone.required' => 'Informe o telefone do locatário.',
            'dataNascimento.required' => 'Informe a data de nascimento.',
            'dataNascimento.before_or_equal' => 'O locatário deve ser maior de 18 anos.',
            'estadoCivil.required' => 'Informe o estado civil.',
            'estadoCivil.enum' => 'Estado civil inválido.',
            'renda.required' => 'Informe a renda.',
            'endereco.required' => 'Informe o endereço.',
        ];
    }

    public function toDto(): ConverterLeadData
    {
        return ConverterLeadData::fromValidated($this->validated());
    }
}
