<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarImovelRequest extends RequisicaoBase
{
    use NormalizaCamposDoImovel;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['titulo', 'logradouro', 'numero', 'bairro', 'cidade'] as $campo) {
            if ($this->has($campo)) {
                $normalizados[$campo] = $this->textoLimitado($this->input($campo));
            }
        }

        if ($this->has('cep')) {
            $normalizados['cep'] = $this->somenteDigitos($this->input('cep'));
        }

        if ($this->has('estado')) {
            $normalizados['estado'] = $this->estadoNormalizado($this->input('estado'));
        }

        $this->merge($normalizados);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'required', 'string', 'min:3', 'max:150'],
            'nomeProprietario' => ['sometimes', 'nullable', 'string', 'max:150'],
            'tipo' => ['sometimes', 'required', Rule::enum(TipoImovel::class)],
            'cep' => ['sometimes', 'required', 'digits:8'],
            'logradouro' => ['sometimes', 'required', 'string', 'max:180'],
            'numero' => ['sometimes', 'required', 'string', 'max:20'],
            'complemento' => ['sometimes', 'nullable', 'string', 'max:120'],
            'bairro' => ['sometimes', 'nullable', 'string', 'max:120'],
            'cidade' => ['sometimes', 'required', 'string', 'max:120'],
            'estado' => ['sometimes', 'nullable', 'string', 'size:2'],
            'area' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'quartos' => ['sometimes', 'required', 'integer', 'min:0'],
            'banheiros' => ['sometimes', 'required', 'integer', 'min:0'],
            'vagasGaragem' => ['sometimes', 'required', 'integer', 'min:0'],
            'valorAluguel' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'valorIptu' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'valorCondominio' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'aguaInclusa' => ['sometimes', 'nullable', 'boolean'],
            'energiaInclusa' => ['sometimes', 'nullable', 'boolean'],
            'internetInclusa' => ['sometimes', 'nullable', 'boolean'],
            'gasIncluso' => ['sometimes', 'nullable', 'boolean'],
            'matricula' => ['sometimes', 'nullable', 'string', 'max:120'],
            'descricao' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cep.digits' => 'CEP deve ter 8 dígitos.',
            'estado.size' => 'O estado deve ter 2 letras.',
            'area.gt' => 'Informe a área do imóvel.',
            'valorAluguel.gt' => 'Informe o valor do aluguel.',
            'descricao.max' => 'Descrição muito longa.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'tipo' => 'tipo',
            'cep' => 'CEP',
            'logradouro' => 'logradouro',
            'numero' => 'número',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'estado' => 'estado',
            'area' => 'área',
            'quartos' => 'quartos',
            'banheiros' => 'banheiros',
            'vagasGaragem' => 'vagas de garagem',
            'valorAluguel' => 'valor do aluguel',
            'valorIptu' => 'valor do IPTU',
            'valorCondominio' => 'valor do condomínio',
            'matricula' => 'matrícula',
            'descricao' => 'descrição',
        ];
    }
}
