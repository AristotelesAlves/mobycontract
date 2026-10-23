<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Support\Facades\Config;

abstract class RequisicaoDeInquilino extends RequisicaoBase
{
    use NormalizaCamposDoInquilino;
    use RegrasDeDocumento;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['nome', 'rg', 'email', 'telefone', 'dataNascimento', 'profissao', 'endereco'] as $campo) {
            if ($this->has($campo)) {
                $normalizados[$campo] = $this->textoNormalizado($this->input($campo));
            }
        }

        if ($this->has('cpf')) {
            $normalizados['cpf'] = $this->somenteDigitos($this->input('cpf'));
        }

        if ($this->has('tipoPessoa')) {
            $tipo = $this->textoNormalizado($this->input('tipoPessoa'));
            $normalizados['tipoPessoa'] = $tipo === null ? null : strtoupper($tipo);
        }

        $this->merge($normalizados);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Já existe um inquilino com este CPF.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'E-mail inválido.',
            'email.unique' => 'Já existe um inquilino com este e-mail.',
            'telefone.required' => 'O telefone é obrigatório.',
            'dataNascimento.required' => 'A data de nascimento é obrigatória.',
            'dataNascimento.date_format' => 'Data de nascimento inválida.',
            'dataNascimento.before_or_equal' => 'O locatário deve ser maior de 18 anos.',
            'estadoCivil.required' => 'Selecione o estado civil.',
            'estadoCivil.enum' => 'Selecione o estado civil.',
            'renda.required' => 'Informe uma renda válida.',
            'renda.numeric' => 'Informe uma renda válida.',
            'renda.min' => 'Informe uma renda válida.',
            'endereco.required' => 'O endereço é obrigatório.',
            'rg.max' => 'RG inválido.',
            'clienteId.exists' => 'Locatário de origem inválido.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'cpf' => 'CPF',
            'tipoPessoa' => 'tipo de pessoa',
            'rg' => 'RG',
            'email' => 'e-mail',
            'telefone' => 'telefone',
            'dataNascimento' => 'data de nascimento',
            'estadoCivil' => 'estado civil',
            'profissao' => 'profissão',
            'renda' => 'renda',
            'endereco' => 'endereço',
            'clienteId' => 'origem',
        ];
    }

    protected function dataLimiteDeIdade(): string
    {
        $idadeMinima = Config::integer('mobycontract.inquilinos.idade_minima', 18);

        return now()->subYears($idadeMinima)->format('Y-m-d');
    }
}
